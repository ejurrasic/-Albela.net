<?php
register_asset("scrolltop::css/scrolltop.css");
register_asset("scrolltop::js/scrolltop.js");

register_hook("footer", function() {
   echo view("scrolltop::button");
});