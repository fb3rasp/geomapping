
<div id="olLayerList" class="olLayerList">

	<% cached CategoriesCacheKey %> 
	<form>
		<% if OverlayCategories.Count %>
		<h1 class='category'>Overlays</h1>
		<% control OverlayCategories %>
		<h2 class='category'>$TitleNice</h2>
		<ul class='layers'>
			<% control layers %>
			<li layer="$ID">
				<input type='checkbox' class="" name='layers' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
				<label for="$Title.ATT">
					<span class="title">$TitleNice</span>
				</label>
			</li>
			<% end_control %>
		</ul>
		<% end_control %>
		<% end_if %>

		<% if BackgroundCategories.Count %>
		<h1 class='category'>Background Layers</h1>
		<% control BackgroundCategories %>
		<h2 class='category'>$TitleNice</h2>
		<ul class='layers'>
			<% control layers %>
			<li layer="$ID">
				<input type='checkbox' class="" name='layers' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
				<label for="$Title.ATT">
					<span class="title">$TitleNice</span>
				</label>
			</li>
			<% end_control %>
		</ul>
		<% end_control %>
		<% end_if %>
		
		<% if ContextualCategories.Count %>
		<h1 class='category'>Contextual Layers</h1>
		<% control ContextualCategories %>
		<h2 class='category'>$TitleNice</h2>
		<ul class='layers'>
			<% control layers %>
			<li layer="$ID">
				<input type='radio' class="" name='baselayer' value='$Title' <% if Visible %>checked='checked'<% end_if %> />
				<label for="$Title.ATT">
					<span class="title">$TitleNice</span>
				</label>
			</li>
			<% end_control %>
		</ul>
		<% end_control %>
		<% end_if %>		
	</form>
	<% end_cached %>

</div>
