package com.example.demo.controller.api;

import com.example.demo.model.Article;
import com.example.demo.service.ArticleService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.util.HtmlUtils;

import java.util.Set;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/tags")
public class TagController {

    private final ArticleService articleService;

    public TagController(ArticleService articleService) {
        this.articleService = articleService;
    }

    private Set<String> sanitize(Set<String> tags) {
        return tags.stream()
                .map(HtmlUtils::htmlEscape)
                .collect(Collectors.toSet());
    }

    @GetMapping
    public ResponseEntity<Object> index(@RequestParam(required = false) String name) {
        if (name != null) {
            Set<Article> matches = articleService.findByTag(name);
            return ResponseEntity.ok(matches);
        }
        return ResponseEntity.ok(articleService.allTags());
    }

    @GetMapping("/article/{id}")
    public ResponseEntity<Object> tagsForArticle(@PathVariable String id) {
        return articleService.findById(id)
                .<ResponseEntity<Object>>map(a -> ResponseEntity.ok(sanitize(a.getTags())))
                .orElseGet(() -> ResponseEntity.notFound().build());
    }

    @PutMapping("/article/{id}")
    public ResponseEntity<Object> updateTags(@PathVariable String id,
                                             @RequestBody String[] tags) {
        if (!articleService.setTags(id, tags)) return ResponseEntity.notFound().build();
        return articleService.findById(id)
                .<ResponseEntity<Object>>map(a -> ResponseEntity.ok(sanitize(a.getTags())))
                .orElseGet(() -> ResponseEntity.notFound().build());
    }
}
