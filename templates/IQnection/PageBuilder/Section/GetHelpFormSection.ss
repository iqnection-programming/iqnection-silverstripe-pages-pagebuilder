
	<div class="wrap $CSSClasses" id="pageBuilder-section-{$ID}">
		<div class="page-width">
			<% if $Headline %>
				<h2 class="title">$Headline</h2>
			<% end_if %>
			<% if $Content %>
				<div class="content">$Content</div>
			<% end_if %>
			<div class="flex flex--half">
				<div class="flex--right" style="background-image:url(<% if $Image.Exists %>$Image.FitMax(600,600).URL<% end_if %>);">
					<p>No Charge Consolutation. No Ongoing Fees.</p>
				</div>
				<div class="flex--left">
					<div class="help-form">
						$HelpContactForm
					</div>
				</div>
			</div>
		</div>
	</div>
