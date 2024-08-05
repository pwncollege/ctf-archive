/* charset "utf-8"; */
/* Pieces: program_image_gallery */
/* base_path_placeholder */
/* @JAVASCRIPT program_image_gallery */
Core.register("file-image-gallery",function(a){return{init:function(){var b=this;$("a.gallery_thumb").click(function(){b.galleryCallback(this);return false});$(document.getElementById("screenshot")).bind("click",function(){var e=$.makeArray($("a.gallery_thumb"));var d=e.length;var c=0;for(;c<d;c++){if(this.href===e[c].href){b.galleryCallback(e[c]);return false}}});a.listen([],this.handleNotification,this)},galleryCallback:function(b){$LAB.script(basePathConfig.programImageGallery).wait(function(){var c=new NyroModalGallery();c.setContainer(document.getElementById("screenshots_gallery")).setTooltipContainer("nyroModalFull").setSelector(".gallery_thumb:not(.screen)").setThumbnailsContainer("nyroModalFull").init(false);c.showModal(b)})},handleNotification:function(b){switch(b.type){}},destroy:function(){a.stopListen([],this)}}});
/* @end JAVASCRIPT program_image_gallery */
/* js_templates_placeholder */