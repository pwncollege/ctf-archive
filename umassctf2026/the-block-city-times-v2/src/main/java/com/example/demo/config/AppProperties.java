package com.example.demo.config;

import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.cloud.context.config.annotation.RefreshScope;
import org.springframework.stereotype.Component;

import java.util.HashMap;
import java.util.Map;
import java.util.Set;

@RefreshScope
@Component
@ConfigurationProperties(prefix = "app")
public class AppProperties {

    private String activeConfig = "prod";
    private boolean enforceProduction = true;
    private Map<String, ConfigSet> configs = new HashMap<>();

    public ConfigSet getActive() {
        return configs.getOrDefault(activeConfig,
                configs.isEmpty() ? new ConfigSet() : configs.values().iterator().next());
    }

    public String getActiveConfig()              { return activeConfig; }
    public void setActiveConfig(String v)        { this.activeConfig = v; }
    public Map<String, ConfigSet> getConfigs()   { return configs; }
    public void setConfigs(Map<String, ConfigSet> v) { this.configs = v; }
    public Set<String> getAvailableConfigs()           { return configs.keySet(); }
    public boolean isEnforceProduction()               { return enforceProduction; }
    public void setEnforceProduction(boolean v)        { this.enforceProduction = v; }

    public static class ConfigSet {
        private String greeting         = "";
        private String environmentLabel = "";

        public String  getGreeting()          { return greeting; }
        public void    setGreeting(String v)  { this.greeting = v; }
        public String  getEnvironmentLabel()          { return environmentLabel; }
        public void    setEnvironmentLabel(String v)  { this.environmentLabel = v; }
    }
}
