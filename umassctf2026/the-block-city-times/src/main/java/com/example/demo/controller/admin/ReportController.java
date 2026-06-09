package com.example.demo.controller.admin;

import com.example.demo.config.AppProperties;
import com.example.demo.config.OutboundProperties;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import org.springframework.http.MediaType;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.client.RestClient;

import java.util.Map;

@Controller
@RequestMapping("/admin/report")
public class ReportController {

    private final AppProperties      appProps;
    private final OutboundProperties outboundProps;
    private final RestClient         restClient;
    private final ObjectMapper       objectMapper;

    public ReportController(AppProperties appProps, OutboundProperties outboundProps,
                            RestClient.Builder builder, ObjectMapper objectMapper) {
        this.appProps      = appProps;
        this.outboundProps = outboundProps;
        this.restClient    = builder.build();
        this.objectMapper  = objectMapper;
    }


    @GetMapping
    public String reportPage() {
        return "redirect:/admin";
    }

    @PostMapping
    public String reportError(@RequestParam(defaultValue = "/api/config") String endpoint,
                              Model model) {
        if (!appProps.getActiveConfig().equals("dev")) {
            return "redirect:/admin?error=reportdevonly";
        }
        if (!endpoint.startsWith("/api/")) {
            return "redirect:/admin?error=reportbadendpoint";
        }

        model.addAttribute("ticker",         appProps.getActive().getGreeting());
        model.addAttribute("activeConfig",   appProps.getActiveConfig());
        model.addAttribute("reportEndpoint", endpoint);

        try {
            String raw = restClient.post()
                    .uri(outboundProps.getReportUrl())
                    .contentType(MediaType.APPLICATION_JSON)
                    .body(Map.of("endpoint", endpoint))
                    .retrieve()
                    .body(String.class);

            JsonNode root = objectMapper.readTree(raw);
            JsonNode reportNode = root.path("report");
            model.addAttribute("reportSuccess", root.path("success").asBoolean(false));
            model.addAttribute("reportJson",    reportNode.isMissingNode() ? ""
                    : objectMapper.writerWithDefaultPrettyPrinter().writeValueAsString(reportNode));
            model.addAttribute("reportLog",     root.path("log").asText(""));

        } catch (Exception e) {
            model.addAttribute("reportSuccess", false);
            model.addAttribute("reportJson",    "");
            model.addAttribute("reportLog",     "Failed to reach diagnostic runner: " + e.getMessage());
        }

        return "admin/report";
    }
}
