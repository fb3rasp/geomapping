<div class='featureInfoContent'>
<% control Items %>
	<h2>$Layer.Title</h2>
	<% control FeatureTypes %>
		<% control FeatureType %>
			<h3>FeatureType: $Name</h3>
		<% end_control %>

		<h4>Features:</h4>
		<hr/>
		<% control Features %>
			<ul>
			<% control Properties %>
				<li>$key : $value</pli>
			<% end_control %>
			</ul>
			<hr/>
		<% end_control %>
		
	<% end_control %>
<% end_control %>
</div>