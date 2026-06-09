package com.example.demo.controller.web;

import com.example.demo.config.AppProperties;
import com.example.demo.service.ArticleService;
import com.example.demo.service.ViewCountService;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

@Controller
public class NewspaperController {

    private final AppProperties  props;
    private final ArticleService articleService;
    private final ViewCountService viewCountService;

    public NewspaperController(AppProperties props, ArticleService articleService,
                               ViewCountService viewCountService) {
        this.props = props;
        this.articleService = articleService;
        this.viewCountService = viewCountService;
    }

    @GetMapping("/")
    public String frontPage(Model model) {
        var articles = articleService.findAll();
        model.addAttribute("ticker", props.getActive().getGreeting());
        model.addAttribute("featured", articles.get(0));
        model.addAttribute("articles", articles.subList(1, articles.size()));
        return "index";
    }

    @GetMapping("/article/{id}")
    public String article(@PathVariable String id, Model model) {
        return articleService.findById(id).map(article -> {
            viewCountService.recordView(id);
            model.addAttribute("ticker", props.getActive().getGreeting());
            model.addAttribute("article", article);
            return "article";
        }).orElse("redirect:/");
    }

    @GetMapping("/login")
    public String login() {
        return "login";
    }
}
