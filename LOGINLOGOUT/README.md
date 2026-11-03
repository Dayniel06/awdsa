# Java conversion

This project has been converted from the original PHP login/logout app into a Java web application built with Java EE servlets and JSP pages.

## Requirements

- Java 17+
- Maven
- MySQL server
- Tomcat 10 or another servlet-compatible Java server

## Database setup

1. Create the database and table using `database.sql` in MySQL.
2. Verify the database connection details in `src/main/java/com/example/loginlogout/DatabaseUtil.java`.

## Run the app

```bash
mvn package
```

Deploy the generated WAR file from `target/loginlogout.war` to Tomcat, or run it in a compatible servlet container.

Open the app using the deployed context path, usually:

```text
http://localhost:8080/loginlogout/
```

The original PHP functionality is preserved: registration, sign-in, CSRF protection, session handling, and logout.