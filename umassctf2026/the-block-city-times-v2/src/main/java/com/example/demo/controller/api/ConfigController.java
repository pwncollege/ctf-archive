package com.example.demo.controller.api;

import com.example.demo.config.AppProperties;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.LinkedHashMap;
import java.util.Map;

@RestController
@RequestMapping("/api")
public class ConfigController {

    private final AppProperties props;

    public ConfigController(AppProperties props) {
        this.props = props;
    }

    @GetMapping("/config")
    public Map<String, Object> config() {
        var active = props.getActive();
        Map<String, Object> result = new LinkedHashMap<>();
        result.put("activeConfig",      props.getActiveConfig());
        result.put("availableConfigs",  props.getAvailableConfigs());
        result.put("greeting",          active.getGreeting());
        result.put("environmentLabel",  active.getEnvironmentLabel());
        return result;
    }
}
