
<div class="content-builder-container">
	<% loop $ContentBuilderBlocks %>
		<div class="content-builder-block $SectionCSSClasses" id="$ElementHTMLID">
			$Render
		</div>
	<% end_loop %>
</div>
