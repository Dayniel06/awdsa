<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="java.util.UUID" %>
<%!
    private String escapeHtml(String value) {
        if (value == null) {
            return "";
        }
        return value
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\"", "&quot;")
            .replace("'", "&#39;");
    }
%>
<%
    HttpSession httpSession = request.getSession(true);
    String csrfToken = (String) httpSession.getAttribute("csrf_token");
    if (csrfToken == null || csrfToken.isBlank()) {
        csrfToken = UUID.randomUUID().toString().replace("-", "");
        httpSession.setAttribute("csrf_token", csrfToken);
    }

    Object flashValue = httpSession.getAttribute("flash");
    String flashMessage = flashValue == null ? "" : String.valueOf(flashValue);
    httpSession.removeAttribute("flash");

    String form = "signup".equals(request.getParameter("form")) ? "signup" : "signin";
    boolean signedIn = httpSession.getAttribute("user_id") != null;
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &amp; Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<%= request.getContextPath() %>/style.css">
</head>
<body>
<% if (signedIn) { %>
    <div class="container">
        <h1 class="form-title">Signed In</h1>
        <p class="account-message">Welcome, <%= escapeHtml(String.valueOf(httpSession.getAttribute("user_name"))) %>.</p>
        <form method="post" action="<%= request.getContextPath() %>/auth">
            <input type="hidden" name="csrf_token" value="<%= escapeHtml(csrfToken) %>">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="btn">Log Out</button>
        </form>
    </div>
<% } else { %>
    <div class="container" id="signup" style="<%= "signup".equals(form) ? "" : "display: none;" %>">
        <h1 class="form-title">Register</h1>

        <% if (!flashMessage.isEmpty() && "signup".equals(form)) { %>
            <p class="form-message" role="status"><%= escapeHtml(flashMessage) %></p>
        <% } %>

        <form method="post" action="<%= request.getContextPath() %>/auth">
            <input type="hidden" name="csrf_token" value="<%= escapeHtml(csrfToken) %>">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="first_name" id="Fname" placeholder="First Name" autocomplete="given-name" maxlength="100" required>
                <label for="Fname">First Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="last_name" id="Lname" placeholder="Last Name" autocomplete="family-name" maxlength="100" required>
                <label for="Lname">Last Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" autocomplete="email" maxlength="191" required>
                <label for="email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" autocomplete="new-password" minlength="8" required>
                <label for="password">Password</label>
            </div>

            <button type="submit" class="btn" name="action" value="register">Sign Up</button>
        </form>

        <p class="or">-------------- or --------------</p>
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
        <div class="links">
            <p>Already have an account?</p>
            <button type="button" id="signInButton">Sign In</button>
        </div>
    </div>

    <div class="container" id="signIn" style="<%= "signup".equals(form) ? "display: none;" : "" %>">
        <h1 class="form-title">Sign In</h1>

        <% if (!flashMessage.isEmpty() && "signin".equals(form)) { %>
            <p class="form-message" role="status"><%= escapeHtml(flashMessage) %></p>
        <% } %>

        <form method="post" action="<%= request.getContextPath() %>/auth">
            <input type="hidden" name="csrf_token" value="<%= escapeHtml(csrfToken) %>">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="loginEmail" placeholder="Email" autocomplete="email" required>
                <label for="loginEmail">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="loginPassword" placeholder="Password" autocomplete="current-password" required>
                <label for="loginPassword">Password</label>
            </div>

            <button type="submit" class="btn" name="action" value="signin">Sign In</button>
        </form>

        <p class="or">-------------- or --------------</p>
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
        <div class="links">
            <p>Don't have an account yet?</p>
            <button type="button" id="signUpButton">Sign Up</button>
        </div>
    </div>
<% } %>

<script src="<%= request.getContextPath() %>/script.js"></script>
</body>
</html>
