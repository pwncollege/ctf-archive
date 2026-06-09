package com.example.demo.service;

import com.example.demo.model.Article;
import org.springframework.stereotype.Service;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Optional;
import java.util.Set;
import java.util.stream.Collectors;

@Service
public class ArticleService {

    private static final Set<Article> ARTICLES = Set.of(
        new Article("1", "LOCAL NEWS", "#c0392b",
            "Local Man Falls into Block City Canal",
            "Björn Blockenson, 32, saved by timely rescue from the Block City Aerial Fire Rescue Brigade",
            "By Sturdwick Platesworth", "April 10, 2026",
            "CANAL DISTRICT CENTRAL — Yesterday afternoon, local actuary Björn Blockenson was rescued by" +
            "firefighters from the Block City Aerial Fire Rescue Brigade after slipping into the Block City " +
            "canal.\n\n" +
            "The rescuers were onto the scene within 4 minutes thanks to the Block City fire department's newly " +
            "aquired fleet of Airblock H67 \"Super Axle\" utility helicopters. Videos of the firefighters extracting " +
            "Björn from the canal via hanging stretcher soon circulated on Bricktok and Platebook, commending the " +
            "firefighters for their heroic actions.\"\n\n" +
            "Fire Chief Pickford Studson later made a statement attributing the rescue to the responsible allotment of " +
            "municipal funds towards public safety resources.",
            true,
            Set.of("rescue", "canal", "fire-brigade", "aerial")),
        new Article("2", "ECONOMY", "#8e44ad",
            "Transparent Brick Shortage Deepens as Colour Council Weighs Emergency Rationing",
            "Clear and frosted plate depletion blamed; specialty-piece speculators implicated",
            "By Crystalinda Bricksworth", "April 10, 2026",
            "COLOUR DISTRICT — The Block City Brick Exchange reported a 34% price surge this week as " +
            "warehouse surveys confirmed widespread depletion of transparent and frosted plate stocks " +
            "across the three primary Block City distribution depots after the United Studs - Blockran " +
            "war has entered into its sixth week.\n\n" +
            "The Colour Council convened an emergency session on Tuesday, with Supply Master Flintwick " +
            "Cornerstone warning that window-panel shortages could stall construction projects within " +
            "sixty days if current draw-down rates continue.\n\n" +
            "Ordinary builders have begun hoarding standard red two-by-fours as a hedge. The queue " +
            "outside the Central Parts Depot stretched four baseplates deep by midday.",
            false,
            Set.of("shortage", "colour-council", "speculation", "war")),
        new Article("3", "TECHNOLOGY", "#e67e22",
            "Master Builder Wins Grand Innovation Prize for Self-Sustaining Gear Clock",
            "Design eliminates manual winding — could transform automated build lines across all districts",
            "By Leverwick Cogsworth", "April 9, 2026",
            "TECHNIC QUARTER — Master Builder Greta Axlepin was awarded the Grand Innovation Prize at a " +
            "ceremony in Brick District Central last night for her self-sustaining gear clock, a " +
            "mechanism that has already been reproduced in over 200 automated assembly installations.\n\n" +
            "The key breakthrough is a worm-gear feedback loop that detects torque loss before it " +
            "propagates to connected conveyor arms — allowing the clock to self-correct without any " +
            "figure intervention.\n\n" +
            "\"Previous designs would lose synchronisation after roughly 48 hours of continuous " +
            "operation,\" she told the Block City Times. \"Mine has been running for eleven months without " +
            "a single manual wind.\"\n\n" +
            "Critics note the design requires 47 crown gears and is sensitive to baseplate flex under " +
            "heavy load. Axlepin says a simplified 12-gear variant is already in field testing.",
            false,
            Set.of("innovation", "gear-clock", "technic-quarter", "automation")),
        new Article("4", "PUBLIC SAFETY", "#27ae60",
            "Third Loose-Stud Collapse This Month Damages Market Sector",
            "Colour Council debates mandatory clutch inspections after connection ordinance fails vote",
            "By Brickwall Patchington", "April 8, 2026",
            "MARKET SECTOR — A structural failure caused by loose-stud syndrome in the early hours of " +
            "Wednesday brought down three market stalls and a partially completed plate-wall warehouse " +
            "in the northern Market Sector, marking the third such incident in thirty days.\n\n" +
            "No figures were reported damaged, though a vendor setting out early for a tile exchange " +
            "was taken to the district repair station suffering from misaligned joints.\n\n" +
            "Connection Officer Torchwick Greyplate confirmed the collapse is under investigation. " +
            "Preliminary findings suggest the lower courses had been assembled without proper alternating " +
            "bond — a known weak-point when builders skip every other offset course.\n\n" +
            "The proposed ordinance requiring clutch-strength inspections on all load-bearing walls " +
            "taller than eight bricks failed its second Council vote last month over objections from " +
            "the Independent Builders' Assembly regarding inspection costs.",
            false,
            Set.of("collapse", "market-sector", "loose-stud", "council")),
        new Article("5", "CONSTRUCTION", "#2980b9",
            "SNOT Technique Triples Build Stability, Landmark Study Confirms",
            "Traditional stackers resistant; Master Builders call findings 'impossible to ignore'",
            "By Deeprock Plateford", "April 7, 2026",
            "BUILDERS' CONSORTIUM — A peer-reviewed study published in the Quarterly Journal of " +
            "Structural Brickwork has confirmed that the Studs-Not-On-Top method pioneered by " +
            "Consortium engineer Hammersby Connectorfield achieves 3.1 times greater lateral " +
            "stability per course than conventional stud-up stacking.\n\n" +
            "The method, which orients every third course sideways using bracket plates to maintain " +
            "connectivity, distributes shear load across multiple axes rather than concentrating it " +
            "at vertical stud columns.\n\n" +
            "\"Most builders are still using techniques from before the Great Baseplate Expansion,\" " +
            "said lead researcher Pickaxe Studfield. \"The data is impossible to ignore. Adapt or " +
            "watch your walls lean.\"\n\n" +
            "The Traditional Stackers' Union has formally disputed the study, calling the lateral " +
            "load claims 'laboratory conditions only' and the comparison methodology 'unfair to " +
            "builders working without bracket plates.'",
            false,
            Set.of("snot", "stability", "research", "consortium"))
    );

    public List<Article> findAll() {
        return new ArrayList<>(ARTICLES);
    }

    public Optional<Article> findById(String id) {
        return ARTICLES.stream().filter(a -> a.getId().equals(id)).findFirst();
    }

    public boolean setTags(String id, String[] tags) {
        return findById(id).map(a -> { a.setTags(tags); return true; }).orElse(false);
    }

    public Set<Article> findByTag(String tag) {
        return Set.of(ARTICLES.stream()                                                                                                                                                           
            .filter(a -> a.getTags().contains(tag))                                                                                                                                           
            .toArray(Article[]::new)); 
    }

    public Set<String> allTags() {                                                                                                                                                                        
        return Set.of(ARTICLES.stream()
            .flatMap(a -> a.getTags().stream())                                                                                                                           
            .toArray(String[]::new));                                                                                                                                                                
    }  
}
