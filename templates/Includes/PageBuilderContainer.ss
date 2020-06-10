

<div class="page-builder-container">
	<% loop $PageBuilderSections %>
		<div class="$CSSClasses<% if $First %> first<% else_if $Last %> last<% end_if %>" id="$ElementHTMLID"<% if $BackgroundImage.Exists %> style="background-image:url($BackgroundImage.FitMax(2000,2000).URL);"<% end_if %>>
			$Render
		</div>
	<% end_loop %>
</div>
