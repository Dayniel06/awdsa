package com.example.loginlogout;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;
import org.mindrot.jbcrypt.BCrypt;

import java.io.IOException;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

@WebServlet(name = "AuthServlet", urlPatterns = {"/auth"})
public class AuthServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws IOException {
        redirectToForm(request, response, "signin");
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        HttpSession session = request.getSession(true);
        String action = normalizeAction(request.getParameter("action"));
        String form = "register".equals(action) ? "signup" : "signin";

        if (!isValidCsrfToken(request, session)) {
            failWithMessage(request, response, "Your session expired. Please try again.", form);
            return;
        }

        switch (action) {
            case "logout":
                handleLogout(request, response, session);
                return;
            case "register":
                handleRegister(request, response, session);
                return;
            case "signin":
                handleSignin(request, response, session);
                return;
            default:
                redirectToForm(request, response, "signin");
                return;
        }
    }

    private void handleLogout(HttpServletRequest request, HttpServletResponse response, HttpSession session)
            throws IOException {
        session.invalidate();
        redirectToForm(request, response, "signin");
    }

    private void handleRegister(HttpServletRequest request, HttpServletResponse response, HttpSession session)
            throws IOException {
        String firstName = trimToNull(request.getParameter("first_name"));
        String lastName = trimToNull(request.getParameter("last_name"));
        String email = normalizeEmail(request.getParameter("email"));
        String password = request.getParameter("password");

        if (firstName == null || firstName.length() > 100 || lastName == null || lastName.length() > 100) {
            failWithMessage(request, response, "Enter a first and last name, up to 100 characters each.", "signup");
            return;
        }

        if (email == null || email.length() > 191 || !isEmailAddress(email)) {
            failWithMessage(request, response, "Enter a valid email address.", "signup");
            return;
        }

        if (password == null || password.length() < 8) {
            failWithMessage(request, response, "Use a password with at least 8 characters.", "signup");
            return;
        }

        String passwordHash = BCrypt.hashpw(password, BCrypt.gensalt());

        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(
                     "INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)")) {
            statement.setString(1, firstName);
            statement.setString(2, lastName);
            statement.setString(3, email);
            statement.setString(4, passwordHash);
            statement.executeUpdate();
        } catch (SQLException exception) {
            if (isDuplicateEmailException(exception)) {
                failWithMessage(request, response, "An account with that email already exists.", "signup");
                return;
            }
            failWithMessage(request, response, "The database is unavailable. Check that MySQL is running and configured.", "signup");
            return;
        }

        failWithMessage(request, response, "Account created. You can now sign in.", "signin");
    }

    private void handleSignin(HttpServletRequest request, HttpServletResponse response, HttpSession session)
            throws IOException {
        String email = normalizeEmail(request.getParameter("email"));
        String password = request.getParameter("password");

        if (email == null || !isEmailAddress(email) || password == null || password.isEmpty()) {
            failWithMessage(request, response, "Email or password is incorrect.", "signin");
            return;
        }

        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(
                     "SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?")) {
            statement.setString(1, email);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (!resultSet.next()) {
                    failWithMessage(request, response, "Email or password is incorrect.", "signin");
                    return;
                }

                int userId = resultSet.getInt("id");
                String firstName = resultSet.getString("first_name");
                String lastName = resultSet.getString("last_name");
                String storedHash = resultSet.getString("password_hash");

                if (!BCrypt.checkpw(password, storedHash)) {
                    failWithMessage(request, response, "Email or password is incorrect.", "signin");
                    return;
                }

                session.invalidate();
                HttpSession newSession = request.getSession(true);
                newSession.setAttribute("user_id", userId);
                newSession.setAttribute("user_name", firstName + " " + lastName);
                redirectToForm(request, response, "");
                return;
            }
        } catch (SQLException exception) {
            failWithMessage(request, response, "The database is unavailable. Check that MySQL is running and configured.", "signin");
        }
    }

    private void failWithMessage(HttpServletRequest request, HttpServletResponse response, String message, String form)
            throws IOException {
        HttpSession session = request.getSession(true);
        session.setAttribute("flash", message);
        redirectToForm(request, response, form);
    }

    private void redirectToForm(HttpServletRequest request, HttpServletResponse response, String form)
            throws IOException {
        StringBuilder target = new StringBuilder(request.getContextPath()).append('/');
        if ("signup".equals(form)) {
            target.append("?form=signup");
        }
        response.sendRedirect(response.encodeRedirectURL(target.toString()));
    }

    private boolean isValidCsrfToken(HttpServletRequest request, HttpSession session) {
        String expected = (String) session.getAttribute("csrf_token");
        String actual = request.getParameter("csrf_token");
        if (expected == null || actual == null) {
            return false;
        }
        return MessageDigest.isEqual(expected.getBytes(StandardCharsets.UTF_8), actual.getBytes(StandardCharsets.UTF_8));
    }

    private String normalizeAction(String rawAction) {
        if (rawAction == null) {
            return "";
        }
        String trimmed = rawAction.trim();
        if ("Sign Up".equals(trimmed)) {
            return "register";
        }
        if ("Sign In".equals(trimmed)) {
            return "signin";
        }
        return trimmed;
    }

    private String normalizeEmail(String email) {
        if (email == null) {
            return null;
        }
        String trimmed = email.trim();
        return trimmed.isEmpty() ? null : trimmed.toLowerCase();
    }

    private String trimToNull(String value) {
        if (value == null) {
            return null;
        }
        String trimmed = value.trim();
        return trimmed.isEmpty() ? null : trimmed;
    }

    private boolean isEmailAddress(String email) {
        String pattern = "^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$";
        return email != null && email.matches(pattern);
    }

    private boolean isDuplicateEmailException(SQLException exception) {
        return exception.getSQLState() != null && exception.getSQLState().startsWith("23");
    }
}
