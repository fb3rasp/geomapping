
<div id="olLayerList" class="olLayerList">
	<% cached CategoriesCacheKey %> 
	<form>
		<% control Categories %>
		<h2 class='category'>$TitleNice</h2>
		
		<% if OverlayLayersEnabledAndVisible.Count %>
		<ul class='layers'>
			<% control OverlayLayersEnabledAndVisible %>
			<li layer="$ID">
				<input type='checkbox' class="checkbox" name='layers' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
				<label for="$Title.ATT">
					<span class="title">$TitleNice</span>
				</label>
			</li>
			<% end_control %>
		</ul>
		<% end_if %>
		<% end_control %>
	</form>
	<% end_cached %>

</div>
