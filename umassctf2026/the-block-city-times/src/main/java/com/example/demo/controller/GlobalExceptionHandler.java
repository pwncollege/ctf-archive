package com.example.demo.controller;

import com.example.demo.config.AppProperties;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.servlet.resource.NoResourceFoundException;

@ControllerAdvice
public class GlobalExceptionHandler {

    private final AppProperties props;

    public GlobalExceptionHandler(AppProperties props) {
        this.props = props;
    }

    @ExceptionHandler(NoResourceFoundException.class)
    public ResponseEntity<String> handle404() {
        return ResponseEntity.status(HttpStatus.NOT_FOUND)
                .body("404 Not Found");
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity<String> handle500(Exception ex) {
        boolean isDev = "dev".equals(props.getActiveConfig());
        String body = isDev && ex.getMessage() != null
                ? "500 Internal Server Error: " + ex.getMessage()
                : "500 Internal Server Error";
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(body);
    }
}
