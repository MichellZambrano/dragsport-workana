$(document).ready(function(){var a=$(".skills-item");a.each(function(){a.appear({force_process:!0}),a.on("appear",function(){var a=$(this);a.data("inited")||(a.find(".skills-item-meter-active").fadeTo(300,1).addClass("skills-animate"),a.data("inited",!0))})})});