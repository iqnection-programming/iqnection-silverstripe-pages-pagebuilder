
<div class="wrap $CSSClasses" id="pageBuilder-section-{$ID}">
	<div class="page-width">
		<% loop $ContentBuilderBlocks %>
			<div class="content-builder-block $SectionCSSClasses" id="contentBuilder-block-{$ID}">
				$Render
			</div>
		<% end_loop %>
	</div>
</div>
