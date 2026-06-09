package com.example.demo.controller.api;

import com.example.demo.service.ArticleService;
import com.example.demo.service.ViewCountService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.LinkedHashMap;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/metrics")
public class MetricsController {

    private final ViewCountService viewCountService;
    private final ArticleService   articleService;

    public MetricsController(ViewCountService viewCountService, ArticleService articleService) {
        this.viewCountService = viewCountService;
        this.articleService   = articleService;
    }

    @GetMapping
    public Map<String, Object> metrics() {
        var articleViews = viewCountService.getAllArticleViews();

        var topArticles = articleService.findAll().stream()
                .sorted((a, b) -> Long.compare(
                        articleViews.getOrDefault(b.getId(), 0L),
                        articleViews.getOrDefault(a.getId(), 0L)))
                .limit(3)
                .map(a -> Map.of(
                        "id",    a.getId(),
                        "title", a.getHeadline(),
                        "views", articleViews.getOrDefault(a.getId(), 0L)))
                .collect(Collectors.toList());

        var dailyStats = viewCountService.getDailyStats();
        var dailyMap   = new LinkedHashMap<String, Long>();
        dailyStats.forEach(s -> dailyMap.put(s.date().toString(), s.views()));

        return Map.of(
                "totalViews",    viewCountService.getTotalViews(),
                "averagePerDay", Math.round(viewCountService.getAverageViewsPerDay()),
                "todayViews",    dailyStats.isEmpty() ? 0 : dailyStats.get(dailyStats.size() - 1).views(),
                "topArticles",   topArticles,
                "dailyViews",    dailyMap
        );
    }

    @PostMapping("/view/{id}")
    public ResponseEntity<Void> recordView(@PathVariable String id) {
        viewCountService.recordView(id);
        return ResponseEntity.ok().build();
    }
}
