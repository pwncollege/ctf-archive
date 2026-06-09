package com.example.demo.model;

import java.util.Arrays;
import java.util.Collections;
import java.util.HashSet;
import java.util.Set;

public class Article {

    private final String id;
    private final String section;
    private final String sectionColor;
    private final String headline;
    private final String subheadline;
    private final String byline;
    private final String date;
    private final String body;
    private final boolean featured;
    private Set<String> tags;

    public Article(String id, String section, String sectionColor,
                   String headline, String subheadline,
                   String byline, String date, String body, boolean featured,
                   Set<String> tags) {
        this.id = id;
        this.section = section;
        this.sectionColor = sectionColor;
        this.headline = headline;
        this.subheadline = subheadline;
        this.byline = byline;
        this.date = date;
        this.body = body;
        this.featured = featured;
        this.tags = new HashSet<>(tags);
    }

    public String getId()            { return id; }
    public String getSection()       { return section; }
    public String getSectionColor()  { return sectionColor; }
    public String getHeadline()      { return headline; }
    public String getSubheadline()   { return subheadline; }
    public String getByline()        { return byline; }
    public String getDate()          { return date; }
    public String getBody()          { return body; }
    public String[] getParagraphs()  { return body.split("\n\n"); }
    public boolean isFeatured()      { return featured; }
    public Set<String> getTags() { return Collections.unmodifiableSet(tags); }

    public void setTags(String[] replacement) {
        this.tags = new HashSet<>(Arrays.asList(replacement));
    }
}
