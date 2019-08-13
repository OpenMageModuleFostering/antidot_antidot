<?php
/** @file raw_example.php
 * @example raw_example.php
 */
require_once "/home/ct/Dev/PHP_API/afs_lib.php";
$search = new AfsSearch('3suisses-be.afs-antidot.net', 7123);
$query = $search->build_query_from_url_parameters();
$query = $query->set_lang('fr');  // language is set manually in order to get spellcheck results
$query = $query->set_multi_selection_facets('classification');
$query = $query->set_mono_selection_facets('afs:lang', 'has_variants', 'has_image');
$query = $query->set_facet_order('price_eur', 'marketing', 'classification', 'has_variants', 'has_image');
$query = $query->set_facets_values_sort_order(AfsFacetValuesSortMode::ITEMS, AfsSortOrder::DESC);
$query = $query->set_page(2, 'Catalog');
$helper = $search->execute($query);
$generated_url = $search->get_generated_url();
$clustering_is_active = $query->has_cluster();

$reply_set = $helper->get_replyset('Catalog');
$pager = $reply_set->get_pager();
$pages = $pager->get_all_pages();

if ($helper->has_replyset()) {
    $replyset = $helper->get_replyset('Catalog'); // Retrieves only first replyset
    if ($replyset->has_facet()) {
        foreach ($replyset->get_facets() as $facet) {
            foreach ($facet->get_labels() as $lang => $label) {
                echo $facet->id + " " + $lang + " " + $label + "\n";
            }
        }
    }
}