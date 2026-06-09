package com.example.demo.config;

import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.stereotype.Component;

import java.util.List;

@Component
@ConfigurationProperties(prefix = "app.outbound")
public class OutboundProperties {

    private String editorialUrl  = "";
    private String reportUrl     = "";
    private List<String> allowedTypes = List.of("text/plain");

    public String getEditorialUrl()                { return editorialUrl; }
    public void setEditorialUrl(String v)          { this.editorialUrl = v; }
    public String getReportUrl()                   { return reportUrl; }
    public void setReportUrl(String v)             { this.reportUrl = v; }
    public List<String> getAllowedTypes()           { return allowedTypes; }
    public void setAllowedTypes(List<String> v)    { this.allowedTypes = v; }
}
