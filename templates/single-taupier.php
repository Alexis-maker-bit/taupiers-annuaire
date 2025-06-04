<?php
/**
 * Template pour l'affichage d'un taupier
 */
get_header();

// Sécurité : Empêche l'accès direct au fichier.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fonction d'aide pour afficher les étoiles de notation.
 *
 * @param float $average_rating La note moyenne.
 * @param bool  $schema_org Indique si les microdonnées Schema.org doivent être incluses.
 * @return string Le HTML des étoiles.
 */
function taupier_display_stars($average_rating, $schema_org = false) {
    $output = '';
    $whole_stars = floor($average_rating);
    $half_star = ($average_rating - $whole_stars) >= 0.25 && ($average_rating - $whole_stars) < 0.75; // Adjusts for better half-star detection
    $empty_stars = 5 - $whole_stars - ($half_star ? 1 : 0);

    if ($schema_org) {
        $output .= '<div class="rating-stars" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">';
        $output .= '<meta itemprop="worstRating" content="1">';
        $output .= '<meta itemprop="bestRating" content="5">';
        $output .= '<meta itemprop="ratingValue" content="' . esc_attr(round($average_rating, 1)) . '">';
    } else {
        $output .= '<div class="rating-stars">';
    }

    // Full stars
    for ($i = 0; $i < $whole_stars; $i++) {
        $output .= '<span class="star full" aria-hidden="true">&#9733;</span>'; // Unicode star
    }

    // Half star
    if ($half_star) {
        $output .= '<span class="star half" aria-hidden="true">&#9733;</span>'; // Unicode star
    }

    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $output .= '<span class="star empty" aria-hidden="true">&#9734;</span>'; // Unicode empty star
    }
    $output .= '</div>'; // .rating-stars

    return $output;
}

?>

<div id="primary" class="content-area taupier-single-container">
    <main id="main" class="site-main">
        <?php
        // Boucle WordPress principale
        if (have_posts()) :
            while (have_posts()) : the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="https://schema.org/LocalBusiness">
            <?php
            // Récupérer les informations de base pour Schema.org et affichage
            $post_id = get_the_ID();
            $taupier_title = get_the_title();
            $taupier_permalink = get_permalink();
            $taupier_content = get_the_content();
            $taupier_excerpt = get_the_excerpt();

            $telephone = get_post_meta($post_id, '_taupier_telephone', true) ?: get_post_meta($post_id, '_taupier_phone', true);
            $email = get_post_meta($post_id, '_taupier_email', true);
            $zone = get_post_meta($post_id, '_taupier_zone', true);
            $experience = get_post_meta($post_id, '_taupier_experience', true);
            $adresse = get_post_meta($post_id, '_taupier_adresse', true);
            $horaires_text = get_post_meta($post_id, '_taupier_horaires', true);
            $ville = get_post_meta($post_id, '_taupier_ville', true);
            $code_postal = get_post_meta($post_id, '_taupier_code_postal', true);

            $main_image_url = '';
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) {
                $image_array = wp_get_attachment_image_src($thumbnail_id, 'full');
                if ($image_array) $main_image_url = $image_array[0];
            }
            if (empty($main_image_url)) {
                $gallery_ids_for_schema = get_post_meta($post_id, '_taupier_gallery_images', true);
                if (is_array($gallery_ids_for_schema) && !empty($gallery_ids_for_schema)) {
                    $first_gallery_image_id = reset($gallery_ids_for_schema);
                    if ($first_gallery_image_id) {
                        $image_array = wp_get_attachment_image_src($first_gallery_image_id, 'full');
                        if ($image_array) $main_image_url = $image_array[0];
                    }
                }
            }

            $reviews_for_schema_args = array(
                'post_type'      => 'taupier_review',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array( 'key' => '_taupier_review_taupier_id', 'value' => $post_id, 'compare' => '=' ),
                    array( 'key' => '_taupier_review_status', 'value' => 'approved', 'compare' => '=' ),
                ),
                'fields' => 'ids' // Plus performant si on ne récupère que les IDs
            );
            $review_ids_for_schema = get_posts($reviews_for_schema_args);
            $count_reviews_schema = count($review_ids_for_schema);
            $average_rating_schema = 0;
            if ($count_reviews_schema > 0) {
                $total_rating_schema = 0;
                foreach ($review_ids_for_schema as $review_id_for_schema) {
                    $total_rating_schema += intval(get_post_meta($review_id_for_schema, '_taupier_review_rating', true));
                }
                $average_rating_schema = round($total_rating_schema / $count_reviews_schema, 1);
            }
            ?>

            <meta itemprop="url" content="<?php echo esc_url($taupier_permalink); ?>">
            <?php if (!empty($main_image_url)) : ?><meta itemprop="image" content="<?php echo esc_url($main_image_url); ?>"><?php endif; ?>
            <meta itemprop="name" content="<?php echo esc_attr($taupier_title); ?>">
            <meta itemprop="description" content="<?php echo esc_attr(wp_trim_words(strip_tags($taupier_excerpt ?: strip_shortcodes($taupier_content)), 30, '...')); ?>">
            <?php if (!empty($telephone)) : ?><meta itemprop="telephone" content="<?php echo esc_attr($telephone); ?>"><?php endif; ?>
            <?php if (!empty($email)) : ?><meta itemprop="email" content="<?php echo esc_attr($email); ?>"><?php endif; ?>

            <?php if (!empty($adresse) || !empty($ville) || !empty($code_postal)) : ?>
            <div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                <?php if (!empty($adresse)) : ?><meta itemprop="streetAddress" content="<?php echo esc_attr($adresse); ?>"><?php endif; ?>
                <?php if (!empty($ville)) : ?><meta itemprop="addressLocality" content="<?php echo esc_attr($ville); ?>"><?php endif; ?>
                <?php if (!empty($code_postal)) : ?><meta itemprop="postalCode" content="<?php echo esc_attr($code_postal); ?>"><?php endif; ?>
                <meta itemprop="addressCountry" content="FR">
            </div>
            <?php endif; ?>
            <meta itemprop="priceRange" content="€€">

            <?php
            // Gestion des horaires pour Schema.org
            if (!empty($horaires_text)) {
                $days_of_week = [
                    'Lundi' => 'http://schema.org/Monday', 'Mardi' => 'http://schema.org/Tuesday', 'Mercredi' => 'http://schema.org/Wednesday',
                    'Jeudi' => 'http://schema.org/Thursday', 'Vendredi' => 'http://schema.org/Friday', 'Samedi' => 'http://schema.org/Saturday', 'Dimanche' => 'http://schema.org/Sunday',
                ];
                $parsed_hours = [];
                if (preg_match_all('/(Lundi|Mardi|Mercredi|Jeudi|Vendredi|Samedi|Dimanche)(?:-(\w+))?:\s*(\d{1,2}h(?:[0-5]\d)?)\s*-\s*(\d{1,2}h(?:[0-5]\d)?)|(7\s*jours\s*sur\s*7|24h\/24)/i', $horaires_text, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        if (!empty($match[5])) { // 7 jours sur 7 ou 24h/24
                             $all_days = array_values($days_of_week);
                             $parsed_hours[] = ['dayOfWeek' => implode(' ', $all_days), 'opens' => '00:00', 'closes' => '23:59'];
                             break; // Prend le dessus
                        } elseif (isset($match[1], $match[3], $match[4])) {
                            $day_start_name = trim($match[1]);
                            $day_end_name = !empty($match[2]) ? trim($match[2]) : $day_start_name;
                            $open_time_formatted = str_pad(str_replace('h', ':', $match[3]), 5, ':00', STR_PAD_RIGHT);
                            $close_time_formatted = str_pad(str_replace('h', ':', $match[4]), 5, ':00', STR_PAD_RIGHT);
                            $start_day_uri = $days_of_week[$day_start_name] ?? '';
                            $end_day_uri = $days_of_week[$day_end_name] ?? '';
                            if ($start_day_uri && $end_day_uri) {
                                $day_uris_map = array_flip(array_keys($days_of_week));
                                $start_index = $day_uris_map[$day_start_name];
                                $end_index = $day_uris_map[$day_end_name];
                                for ($i = $start_index; $i <= $end_index; $i++) {
                                    $current_day_name = array_keys($days_of_week)[$i];
                                    $parsed_hours[] = ['dayOfWeek' => $days_of_week[$current_day_name], 'opens' => $open_time_formatted, 'closes' => $close_time_formatted];
                                }
                            }
                        }
                    }
                }
                if (!empty($parsed_hours)) {
                    foreach ($parsed_hours as $oh_spec) {
                        echo "<div itemprop=\"openingHoursSpecification\" itemscope itemtype=\"https://schema.org/OpeningHoursSpecification\">\n";
                        echo "<meta itemprop=\"dayOfWeek\" content=\"" . esc_attr($oh_spec['dayOfWeek']) . "\">\n";
                        echo "<meta itemprop=\"opens\" content=\"" . esc_attr($oh_spec['opens']) . "\">\n";
                        echo "<meta itemprop=\"closes\" content=\"" . esc_attr($oh_spec['closes']) . "\">\n";
                        echo "</div>\n";
                    }
                } else {
                     echo "<meta itemprop=\"openingHours\" content=\"" . esc_attr(str_replace(["\r\n", "\r", "\n"], ', ', $horaires_text)) . "\">\n"; // Fallback
                }
            }
            ?>

            <?php if ($count_reviews_schema > 0) : ?>
            <div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
                <meta itemprop="ratingValue" content="<?php echo esc_attr($average_rating_schema); ?>">
                <meta itemprop="reviewCount" content="<?php echo esc_attr($count_reviews_schema); ?>">
                <meta itemprop="bestRating" content="5"><meta itemprop="worstRating" content="1">
            </div>
            <?php endif; ?>

            <div class="taupier-row">
                <h1 class="taupier-row-title">Taupier professionnel <span class="entry-title-subtitle" itemprop="name"><?php echo esc_html($taupier_title); ?></span></h1>
                <div class="taupier-row-content">
                    <div class="taupier-main-info-row">
                        <div class="taupier-main-image-column">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="taupier-featured-image">
                                    <?php the_post_thumbnail('large', ['itemprop' => 'image', 'alt' => esc_attr($taupier_title . ' - Photo principale'), 'class' => 'taupier-thumbnail-img']); ?>
                                </div>
                            <?php elseif (!empty($main_image_url)) : ?>
                                 <div class="taupier-featured-image">
                                    <img src="<?php echo esc_url($main_image_url); ?>" alt="<?php echo esc_attr($taupier_title . ' - Photo principale'); ?>" class="taupier-thumbnail-img" itemprop="image">
                                </div>
                            <?php else : ?>
                                <div class="taupier-no-image"><p>Aucune image disponible pour ce taupier.</p></div>
                            <?php endif; ?>
                        </div>
                        <div class="taupier-info-column">
                            <div class="taupier-details">
                                <div class="taupier-coordinates">
                                    <h2>Coordonnées</h2>
                                    <ul class="taupier-info-list">
                                        <?php if (!empty($telephone)) : ?><li><strong>Téléphone :</strong> <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $telephone)); ?>" itemprop="telephone"><?php echo esc_html($telephone); ?></a></li><?php endif; ?>
                                        <?php if (!empty($email)) : ?><li><strong>Email :</strong> <a href="mailto:<?php echo esc_attr($email); ?>" itemprop="email"><?php echo esc_html($email); ?></a></li><?php endif; ?>
                                        <?php if (!empty($zone)) : ?><li><strong>Zone d'intervention :</strong> <span itemprop="areaServed"><?php echo esc_html($zone); ?></span></li><?php endif; ?>
                                        <?php if (!empty($experience)) : ?><li><strong>Expérience :</strong> <span><?php echo esc_html($experience); ?> ans</span></li><?php endif; ?>
                                        <?php if (!empty($adresse) || !empty($ville) || !empty($code_postal)) : ?>
                                            <li><strong>Adresse :</strong> <address><?php if (!empty($adresse)) echo nl2br(esc_html($adresse)); if (!empty($ville)) echo (!empty($adresse) ? '<br>' : '') . '<span itemprop="addressLocality">' . esc_html($ville) . '</span>'; if (!empty($code_postal)) echo ' <span itemprop="postalCode">' . esc_html($code_postal) . '</span>'; ?></address></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php if (!empty($horaires_text)) : ?>
                                    <div class="taupier-hours"><h2>Horaires d'intervention</h2><div class="horaires-content"><?php echo nl2br(esc_html($horaires_text)); ?></div></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="taupier-row">
                <h2 class="taupier-row-title">Description et services proposés</h2>
                <div class="taupier-row-content">
                    <div class="taupier-content entry-content" itemprop="description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <div class="taupier-row">
                <h2 class="taupier-row-title">Galerie photos</h2>
                <div class="taupier-row-content">
                    <div class="taupier-gallery-slider">
                        <?php
                        $gallery_images_ids = get_post_meta($post_id, '_taupier_gallery_images', true);
                        if (!is_array($gallery_images_ids)) { $gallery_images_ids = array(); }
                        
                        $effective_gallery_ids = $gallery_images_ids;
                        // Ajouter l'image mise en avant si elle n'est pas DÉJÀ dans la galerie manuelle
                        if ($thumbnail_id && !in_array($thumbnail_id, $gallery_images_ids)) {
                            array_unshift($effective_gallery_ids, $thumbnail_id);
                        }


                        if (!empty($effective_gallery_ids)) : ?>
                            <div class="swiper gallery-slider-container"> {/* Ajout de la classe swiper ici */}
                                <div class="swiper-wrapper gallery-slider-wrapper">
                                    <?php foreach ($effective_gallery_ids as $index => $image_id) :
                                        $image_medium_large = wp_get_attachment_image_src($image_id, 'medium_large');
                                        $image_full = wp_get_attachment_image_src($image_id, 'full');
                                        if ($image_medium_large && $image_full) : ?>
                                            <div class="swiper-slide gallery-slide" data-index="<?php echo $index; ?>" itemscope itemtype="https://schema.org/ImageObject">
                                                <meta itemprop="contentUrl" content="<?php echo esc_url($image_full[0]); ?>">
                                                <meta itemprop="thumbnailUrl" content="<?php echo esc_url($image_medium_large[0]); ?>">
                                                <meta itemprop="caption" content="<?php echo esc_attr(wp_get_attachment_caption($image_id) ?: get_the_title($image_id)); ?>">
                                                <a href="<?php echo esc_url($image_full[0]); ?>" class="gallery-image-link" data-lightbox="taupier-gallery-<?php echo $post_id; ?>" aria-label="Agrandir l'image <?php echo ($index + 1); ?>">
                                                    <img src="<?php echo esc_url($image_medium_large[0]); ?>" alt="<?php echo esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: ($taupier_title . ' - Image ' . ($index + 1))); ?>" class="gallery-image" loading="lazy" itemprop="image">
                                                </a>
                                            </div>
                                        <?php endif;
                                    endforeach; ?>
                                </div>
                                <?php if (count($effective_gallery_ids) > 1) : ?>
                                    <div class="swiper-button-prev gallery-prev-btn"></div>
                                    <div class="swiper-button-next gallery-next-btn"></div>
                                    <div class="swiper-pagination gallery-dots"></div>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <p>Aucune image disponible pour la galerie.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="taupier-row">
                <h2 class="taupier-row-title">Équipements recommandés</h2>
                <div class="taupier-row-content">
                    <?php
                    global $gestion_taupiers; // Assurez-vous que l'objet est global ou passez-le en paramètre
                    if (isset($gestion_taupiers) && method_exists($gestion_taupiers, 'display_taupe_products_highlighted')) {
                        echo $gestion_taupiers->display_taupe_products_highlighted();
                    } else {
                        echo '<p>La fonctionnalité du slider de produits n\'est pas disponible.</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="taupier-row">
                <h2 class="taupier-row-title">Avis des clients</h2>
                <div class="taupier-row-content">
                    <?php
                    // Récupérer les avis approuvés pour ce taupier
                    $approved_reviews_args = array(
                        'post_type'      => 'taupier_review',
                        'posts_per_page' => -1, // Récupérer tous les avis
                        'meta_query'     => array(
                            'relation' => 'AND',
                            array(
                                'key'     => '_taupier_review_taupier_id',
                                'value'   => $post_id,
                                'compare' => '=',
                            ),
                            array(
                                'key'     => '_taupier_review_status',
                                'value'   => 'approved', // Uniquement les avis approuvés
                                'compare' => '=',
                            ),
                        ),
                        'orderby'        => 'date', // Trier par date de publication de l'avis
                        'order'          => 'DESC',  // Les plus récents en premier
                    );
                    $approved_reviews = get_posts($approved_reviews_args);

                    if (!empty($approved_reviews)) : ?>
                        <div class="taupier-reviews"> {/* itemprop pour aggregateRating est déjà mis plus haut */}
                            <div class="average-rating">
                                <div class="rating-summary">
                                    <span class="rating-value"><?php echo esc_html($average_rating_schema); ?></span><span class="rating-max">/5</span>
                                </div>
                                <?php echo taupier_display_stars($average_rating_schema); ?>
                                <span class="reviews-count">Basé sur <?php echo esc_html($count_reviews_schema); ?> avis</span>
                            </div>
                            <div class="reviews-filters">
                                <div class="filter-options">
                                    <span>Filtrer par : </span>
                                    <button class="filter-btn active" data-filter="all">Tous</button>
                                    <button class="filter-btn" data-filter="positive">Les plus positifs</button>
                                    <button class="filter-btn" data-filter="recent">Les plus récents</button>
                                </div>
                            </div>
                            <div class="reviews-list">
                                <?php foreach ($approved_reviews as $review_post) :
                                    $author_name = get_post_meta($review_post->ID, '_taupier_review_author_name', true);
                                    $rating = get_post_meta($review_post->ID, '_taupier_review_rating', true);
                                    $date = get_the_date('d/m/Y', $review_post->ID);
                                    $iso_date = get_the_date('c', $review_post->ID);
                                ?>
                                <div class="review-item" data-date="<?php echo esc_attr(strtotime($iso_date)); ?>" data-rating="<?php echo esc_attr($rating); ?>" itemprop="review" itemscope itemtype="https://schema.org/Review">
                                    <div class="review-header">
                                        <span class="review-author" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name"><?php echo esc_html($author_name); ?></span></span>
                                        <span class="review-date"><meta itemprop="datePublished" content="<?php echo esc_attr($iso_date); ?>"><?php echo esc_html($date); ?></span>
                                    </div>
                                    <?php echo taupier_display_stars($rating, true); // true pour ajouter le schema.org pour chaque avis ?>
                                    <div class="review-content" itemprop="reviewBody"><?php echo wpautop(esc_html($review_post->post_content)); ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="taupier-reviews-empty"><p>Aucun avis n'a encore été publié pour ce taupier.</p><p>Soyez le premier à donner votre opinion !</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="taupier-faq-form-container">
                <div class="taupier-row taupier-faq-column">
                    <h2 class="taupier-row-title">Questions fréquentes</h2>
                    <div class="taupier-row-content">
                        <?php
                        $faq_items = get_post_meta($post_id, '_taupier_faq', true);
                        $has_valid_faq = false;
                        if (is_array($faq_items)) {
                            foreach($faq_items as $item) {
                                if (!empty($item['question']) && !empty($item['reponse'])) {
                                    $has_valid_faq = true;
                                    break;
                                }
                            }
                        }

                        if ($has_valid_faq) : ?>
                            <div class="taupier-faq" itemscope itemtype="https://schema.org/FAQPage">
                                <div class="faq-search-container"><input type="text" id="faq-search" placeholder="Rechercher une question..." aria-label="Rechercher dans la FAQ"></div>
                                <div class="faq-items">
                                <?php foreach ($faq_items as $qa_pair) :
                                    if (!empty($qa_pair['question']) && !empty($qa_pair['reponse'])) : ?>
                                    <div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                                        <div class="faq-question" itemprop="name"><?php echo esc_html($qa_pair['question']); ?></div>
                                        <div class="faq-reponse" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><div itemprop="text"><?php echo nl2br(esc_html($qa_pair['reponse'])); ?></div></div>
                                    </div>
                                    <?php endif;
                                endforeach; ?>
                                </div>
                                <div id="faq-no-results" style="display: none;">Aucune question ne correspond à votre recherche.</div>
                            </div>
                        <?php else : ?>
                            <div class="taupier-faq-empty"><p>Aucune question fréquente disponible pour ce taupier.</p></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="taupier-row taupier-form-column">
                    <h2 class="taupier-row-title">Laissez votre avis</h2>
                    <div class="taupier-row-content">
                        <div class="taupier-review-form">
                            <form id="submit-taupier-review" action="" method="post">
                                <input type="hidden" name="taupier_id" value="<?php echo esc_attr($post_id); ?>">
                                <div class="form-row">
                                    <div class="form-group"><label for="author_name">Votre nom <span class="required">*</span></label><input type="text" name="author_name" id="author_name" required placeholder="Jean Dupont"></div>
                                    <div class="form-group"><label for="author_email">Votre email <span class="required">*</span></label><input type="email" name="author_email" id="author_email" required placeholder="jean.dupont@example.com"></div>
                                </div>
                                <div class="form-group rating-field">
                                    <label>Votre note <span class="required">*</span></label>
                                    <div class="star-rating">
                                        <input type="radio" id="rating-5" name="rating" value="5" required><label for="rating-5" title="5 étoiles - Excellent"></label>
                                        <input type="radio" id="rating-4" name="rating" value="4"><label for="rating-4" title="4 étoiles - Très bien"></label>
                                        <input type="radio" id="rating-3" name="rating" value="3"><label for="rating-3" title="3 étoiles - Bien"></label>
                                        <input type="radio" id="rating-2" name="rating" value="2"><label for="rating-2" title="2 étoiles - Moyen"></label>
                                        <input type="radio" id="rating-1" name="rating" value="1"><label for="rating-1" title="1 étoile - Mauvais"></label>
                                    </div>
                                    <div class="rating-help">Cliquez sur les étoiles pour noter</div>
                                </div>
                                <div class="form-group">
                                    <label for="review_text">Votre avis <span class="required">*</span></label>
                                    <textarea name="review_text" id="review_text" rows="5" required maxlength="500" placeholder="Partagez votre expérience avec ce taupier..."></textarea>
                                    <div class="textarea-counter">0/500 caractères</div>
                                </div>
                                <button type="submit" class="submit-review-button">Envoyer mon avis</button>
                                <div class="form-message"></div>
                            </form>
                            <div class="review-submission-feedback" style="display: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                <h4 style="color: var(--primary-color);">Merci pour votre avis !</h4>
                                <p>Votre commentaire a été soumis avec succès et sera publié après modération.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <div class="taupier-row">
            <h2 class="taupier-row-title">Autres taupiers dans votre région</h2>
            <div class="taupier-row-content">
                <div class="related-taupiers-slider">
                    <?php
                    $current_taupier_id = get_the_ID();
                    $categories = get_the_terms($current_taupier_id, 'taupier_category');
                    if ($categories && !is_wp_error($categories)) :
                        $category_ids = array_map(function($term) { return $term->term_id; }, $categories);
                        $args_related = array(
                            'post_type' => 'taupier', 'posts_per_page' => 8, 'post__not_in' => array($current_taupier_id),
                            'tax_query' => array(array('taxonomy' => 'taupier_category', 'field' => 'term_id', 'terms' => $category_ids, 'operator' => 'IN')),
                            'orderby' => 'rand', 'no_found_rows' => true,
                            'update_post_meta_cache' => false, 'update_post_term_cache' => false,
                        );
                        $related_taupiers_query = new WP_Query($args_related);

                        if ($related_taupiers_query->have_posts()) : ?>
                            <div class="swiper related-taupier-swiper">
                                <div class="swiper-wrapper">
                                    <?php while ($related_taupiers_query->have_posts()) : $related_taupiers_query->the_post();
                                        $related_taupier_id = get_the_ID();
                                        $related_title = get_the_title();
                                        $related_link = get_permalink();
                                        $related_zone = get_post_meta($related_taupier_id, '_taupier_zone', true);
                                        $related_image_url = has_post_thumbnail() ? get_the_post_thumbnail_url($related_taupier_id, 'medium') : '';
                                        
                                        // Calcul note moyenne pour taupier similaire
                                        $related_reviews_args = array( /* ... */ ); // Vos args pour get_posts
                                        // ... (votre logique de calcul de note pour $avg_related_rating et $count_related_reviews)
                                        $avg_related_rating = 0; // Placeholder
                                        $count_related_reviews = 0; // Placeholder
                                    ?>
                                        <div class="swiper-slide">
                                            <div class="related-taupier-card">
                                                <a href="<?php echo esc_url($related_link); ?>" class="taupier-card-link">
                                                    <?php if (!empty($related_image_url)) : ?>
                                                    <div class="related-taupier-image"><img src="<?php echo esc_url($related_image_url); ?>" alt="<?php echo esc_attr($related_title); ?>" loading="lazy"></div>
                                                    <?php endif; ?>
                                                    <div class="related-taupier-info">
                                                        <h3 class="related-taupier-title"><?php echo esc_html($related_title); ?></h3>
                                                        <?php if (!empty($related_zone)) : ?><p class="related-taupier-zone"><i class="fa fa-map-marker"></i> <?php echo esc_html($related_zone); ?></p><?php endif; ?>
                                                        <?php if ($count_related_reviews > 0) : ?>
                                                        <div class="related-taupier-rating">
                                                            <span class="rating-value"><?php echo esc_html($avg_related_rating); ?></span>
                                                            <?php echo taupier_display_stars($avg_related_rating); ?>
                                                            <span class="reviews-count">(<?php echo esc_html($count_related_reviews); ?>)</span>
                                                        </div>
                                                        <?php endif; ?>
                                                        <div class="related-taupier-button">Voir le profil</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                                <div class="swiper-pagination related-taupier-pagination"></div>
                            </div>
                            <div class="related-slider-controls">
                                <button class="related-prev-btn">&laquo;</button>
                                <button class="related-next-btn">&raquo;</button>
                            </div>
                        <?php else : ?>
                            <p>Aucun autre taupier disponible dans cette catégorie pour le moment.</p>
                        <?php endif; ?>
                    <?php else : ?>
                        <p>Aucune catégorie définie pour ce taupier ou une erreur est survenue.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        else :
            // Contenu à afficher si aucun post n'est trouvé
            get_template_part('template-parts/content', 'none');
        endif;
        ?>
    </main>
</div>

<?php if (!empty($telephone)) : ?>
    <div class="quick-contact-btn">
        <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $telephone)); ?>" class="call-btn" aria-label="Appeler le taupier">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span>Appeler</span>
        </a>
    </div>
<?php endif; ?>

<?php
get_footer();
?>