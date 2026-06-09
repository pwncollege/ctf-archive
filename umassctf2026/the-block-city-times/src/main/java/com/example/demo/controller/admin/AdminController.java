package com.example.demo.controller.admin;

import com.example.demo.config.AppProperties;
import com.example.demo.service.ArticleService;
import com.example.demo.service.ViewCountService;
import org.springframework.cloud.context.refresh.ContextRefresher;
import org.springframework.core.env.MutablePropertySources;
import org.springframework.core.env.ConfigurableEnvironment;
import org.springframework.core.env.MapPropertySource;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;

import java.util.Map;
import java.util.stream.Collectors;

@Controller
@RequestMapping("/admin")
public class AdminController {

    private final AppProperties           props;
    private final ConfigurableEnvironment environment;
    private final ContextRefresher        contextRefresher;
    private final ViewCountService        viewCountService;
    private final ArticleService          articleService;

    public AdminController(AppProperties props, ConfigurableEnvironment environment,
                           ContextRefresher contextRefresher,
                           ViewCountService viewCountService, ArticleService articleService) {
        this.props            = props;
        this.environment      = environment;
        this.contextRefresher = contextRefresher;
        this.viewCountService = viewCountService;
        this.articleService   = articleService;
    }

    @GetMapping({"", "/"})
    public String dashboard(Model model) {
        var active = props.getActive();
        model.addAttribute("ticker",            active.getGreeting());
        model.addAttribute("activeConfig",      props.getActiveConfig());
        model.addAttribute("availableConfigs",  props.getAvailableConfigs());
        model.addAttribute("environmentLabel",  active.getEnvironmentLabel());
        model.addAttribute("enforceProduction", props.isEnforceProduction());
        return "admin/dashboard";
    }

    @GetMapping("/metrics")
    public String metrics(Model model) {
        var active = props.getActive();
        model.addAttribute("ticker",         active.getGreeting());
        model.addAttribute("activeConfig",   props.getActiveConfig());
        model.addAttribute("dailyStats",     viewCountService.getDailyStats());
        model.addAttribute("averagePerDay",  viewCountService.getAverageViewsPerDay());
        model.addAttribute("totalViews",     viewCountService.getTotalViews());
        model.addAttribute("articleViews",   viewCountService.getAllArticleViews());
        model.addAttribute("articles",       articleService.findAll().stream()
                .collect(Collectors.toMap(a -> a.getId(), a -> a)));
        return "admin/metrics";
    }

    @PostMapping("/switch")
    public String switchConfig(@RequestParam String config) {
        if (props.isEnforceProduction()) {
            return "redirect:/admin?error=switchdisabled";
        }
        if (!props.getAvailableConfigs().contains(config)) {
            return "redirect:/admin?error=invalid";
        }
        MutablePropertySources sources = environment.getPropertySources();
        if (sources.contains("switchOverrides")) {
            ((MapPropertySource) sources.get("switchOverrides"))
                    .getSource().put("app.active-config", config);
        } else {
            sources.addFirst(new MapPropertySource("switchOverrides",
                    new java.util.HashMap<>(Map.of("app.active-config", config))));
        }
        contextRefresher.refresh();
        return "redirect:/admin?switched=" + config;
    }
}
