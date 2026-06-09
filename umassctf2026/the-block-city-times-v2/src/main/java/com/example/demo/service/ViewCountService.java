package com.example.demo.service;

import org.springframework.stereotype.Service;

import java.time.LocalDate;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicLong;
import java.util.stream.Collectors;

@Service
public class ViewCountService {

    public record DailyStat(LocalDate date, long views) {}

    private final Map<String, AtomicLong>  articleViews = new ConcurrentHashMap<>();
    private final Map<LocalDate, AtomicLong> dailyViews = new ConcurrentHashMap<>();

    public ViewCountService() {
        articleViews.put("1", new AtomicLong(32104));
        articleViews.put("2", new AtomicLong(11203));
        articleViews.put("3", new AtomicLong(18650));
        articleViews.put("4", new AtomicLong( 7821));
        articleViews.put("5", new AtomicLong( 9442));

        long[] history = { 11823, 13240, 12180, 15720, 14310, 11090, 8950,
                            9340, 14210, 15630, 14720, 12430, 13780, 4712 };
        LocalDate today = LocalDate.now();
        for (int i = 0; i < history.length; i++) {
            dailyViews.put(today.minusDays(13 - i), new AtomicLong(history[i]));
        }
    }

    public void recordView(String articleId) {
        articleViews.computeIfAbsent(articleId, k -> new AtomicLong(0)).incrementAndGet();
        dailyViews.computeIfAbsent(LocalDate.now(), k -> new AtomicLong(0)).incrementAndGet();
    }

    public Map<String, Long> getAllArticleViews() {
        return articleViews.entrySet().stream()
                .sorted(Map.Entry.<String, AtomicLong>comparingByValue(
                        Comparator.comparingLong(AtomicLong::get)).reversed())
                .collect(Collectors.toMap(
                        Map.Entry::getKey,
                        e -> e.getValue().get(),
                        (a, b) -> a,
                        LinkedHashMap::new));
    }

    public List<DailyStat> getDailyStats() {
        LocalDate cutoff = LocalDate.now().minusDays(13);
        return dailyViews.entrySet().stream()
                .filter(e -> !e.getKey().isBefore(cutoff))
                .sorted(Map.Entry.comparingByKey())
                .map(e -> new DailyStat(e.getKey(), e.getValue().get()))
                .collect(Collectors.toList());
    }

    public long getTotalViews() {
        return articleViews.values().stream().mapToLong(AtomicLong::get).sum();
    }

    public double getAverageViewsPerDay() {
        List<DailyStat> stats = getDailyStats();
        if (stats.isEmpty()) return 0;
        return (double) stats.stream().mapToLong(DailyStat::views).sum() / stats.size();
    }
}
