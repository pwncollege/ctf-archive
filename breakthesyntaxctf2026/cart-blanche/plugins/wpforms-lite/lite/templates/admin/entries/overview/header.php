<?php
/**
 * Header row for the Entries List page.
 *
 * @since 1.8.9
 */
?>
<ul class="subsubsub wpforms-input-disabled" aria-disabled="true">
	<li class="all"><a href="#" class="current">All&nbsp;<span class="count">(<span class="total-num">12</span>)</span></a> |</li>
	<li class="unread"><a href="#">Unread&nbsp;<span class="count">(<span class="unread-num">9</span>)</span></a> |</li>
	<li class="starred"><a href="#">Starred&nbsp;<span class="count">(<span class="starred-num">4</span>)</span></a> |</li>
	<li class="spam"><a href="#">Spam <span class="count">(0)</span></a></li>
</ul>
<p class="search-box wpforms-form-search-box wpforms-input-disabled" aria-disabled="true">
	<select name="search[field]" class="wpforms-form-search-box-field">
		<optgroup label="Form fields">
			<option value="any" selected="selected">Any form field</option>
		</optgroup>
	</select>
	<select name="search[comparison]" class="wpforms-form-search-box-comparison">
		<option value="contains" selected="selected">contains</option>
	</select>
	<label class="screen-reader-text" for="wpforms-entries-search-input">
		Search:
	</label>
	<input type="search" name="search[term]" class="wpforms-form-search-box-term" value="" id="wpforms-entries-search-input">
	<button type="submit" class="button">Search</button>
</p>
<div class="tablenav top">
	<?php
	echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'admin/entries/overview/bulk-actions',
		[
			'show_filter' => true,
		],
		true
	);
	?>
</div>
