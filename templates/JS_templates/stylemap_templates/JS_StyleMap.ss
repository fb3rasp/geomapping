
// add stypes for a WFS layer
styles = new OpenLayers.StyleMap({
<% if default %>
    "default": $default.RAW <% if select %>,<% else %><% if temporary %>,<% end_if %><% end_if %>
<% end_if %>
<% if select %>
    "select": $select.RAW <% if temporary %>,<% end_if %>
<% end_if %>
<% if temporary %>
	"temporary": $temporary.RAW
<% end_if %>
});
