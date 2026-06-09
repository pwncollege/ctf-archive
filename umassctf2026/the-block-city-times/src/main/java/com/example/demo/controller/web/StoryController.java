package com.example.demo.controller.web;

import com.example.demo.config.AppProperties;
import com.example.demo.config.OutboundProperties;
import jakarta.annotation.PostConstruct;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.io.FileSystemResource;
import org.springframework.core.io.Resource;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.client.RestClient;
import org.springframework.web.multipart.MultipartFile;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.Map;
import java.util.UUID;

@Controller
public class StoryController {

    private final AppProperties appProps;
    private final OutboundProperties outboundProps;
    private final RestClient restClient;

    @Value("${app.upload-dir:uploads}")
    private String uploadDirConfig;

    private Path uploadDir;

    public StoryController(AppProperties appProps, OutboundProperties outboundProps,
                           RestClient.Builder builder) {
        this.appProps = appProps;
        this.outboundProps = outboundProps;
        this.restClient = builder.build();
    }

    @PostConstruct
    public void init() throws IOException {
        uploadDir = Paths.get(uploadDirConfig).toAbsolutePath().normalize();
        Files.createDirectories(uploadDir);
    }

    @GetMapping("/submit")
    public String submitForm(Model model) {
        model.addAttribute("ticker", appProps.getActive().getGreeting());
        return "submit";
    }

    @PostMapping("/submit")
    public String submitStory(@RequestParam String title,
                              @RequestParam String author,
                              @RequestParam String description,
                              @RequestParam MultipartFile file,
                              Model model) throws IOException {

        model.addAttribute("ticker", appProps.getActive().getGreeting());

        if (file.isEmpty()) {
            model.addAttribute("error", "No file was attached.");
            return "submit";
        }

        String contentType = file.getContentType();
        if (contentType == null || !outboundProps.getAllowedTypes().contains(contentType)) {
            model.addAttribute("error",
                "File type '" + contentType + "' is not accepted. " +
                "Please submit a plain text or PDF document.");
            return "submit";
        }

        String safe = file.getOriginalFilename().replaceAll("[^a-zA-Z0-9._-]", "_");
        String filename = UUID.randomUUID() + "-" + safe;
        Files.write(uploadDir.resolve(filename), file.getBytes());

        Map<String, String> body = Map.of(
            "title",       title,
            "author",      author,
            "description", description,
            "filename",    filename
        );

        ResponseEntity<String> response = restClient.post()
                .uri(outboundProps.getEditorialUrl())
                .contentType(MediaType.APPLICATION_JSON)
                .body(body)
                .retrieve()
                .toEntity(String.class);

        if (response.getStatusCode().is2xxSuccessful()) {
            model.addAttribute("success", true);
            model.addAttribute("submittedTitle", title);
        } else {
            model.addAttribute("error",
                "The system returned an error. Please try again later.");
        }

        return "submit";
    }

    @GetMapping("/files/{filename}")
    public ResponseEntity<Resource> serveFile(@PathVariable String filename) throws IOException {
        Path filePath = uploadDir.resolve(filename).normalize();

        if (!filePath.startsWith(uploadDir)) {
            return ResponseEntity.badRequest().build();
        }

        Resource resource = new FileSystemResource(filePath);
        if (!resource.exists()) {
            return ResponseEntity.notFound().build();
        }

        String contentType = Files.probeContentType(filePath);
        if (contentType == null) contentType = "application/octet-stream";

        return ResponseEntity.ok()
                .contentType(MediaType.parseMediaType(contentType))
                .body(resource);
    }
}
