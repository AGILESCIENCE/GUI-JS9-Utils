
function loadRegionCatalog(regionFileName, format, wcssys, sourceL, sourceB, degreeDistance) {
  $.ajax({
    cache: false,
    url:'js9Utils/regionCatalogForJS9Converter.php',
    type:'get',
    data: {
      "regionFileName" : regionFileName,
      "format" : format,
      "wcssys" : wcssys,
      "sourceL" : sourceL,
      "sourceB" : sourceB,
      "degreeDistance" : degreeDistance
    },
    contentType: "application/json; charset=utf-8",
    async: true

  }).done(function(successResponse) {

    console.log("successResponse: ", successResponse.regionFileName);
    JS9.LoadRegions("js9Utils/"+successResponse.regionFileName);

    // // DEBUG:
    //  console.log("regionFileName: ",regionFileName);
    /*
    $.ajax({
    cache: false,
    type: 'GET',
    url: "js9Utils/"+successResponse.regionFileName,
    contentType: "application/text; charset=utf-8",
    headers: {
        "X-Download":"yes",
    }
    }).done(function(regionFile) {
      console.log(regionFile);
    }).fail(function(xhr, status, error) {
      console.log("ERROR!!",xhr, status, error);
    });
    */

  }).fail(function(errorResponse) {

    console.log(errorResponse.responseJSON);
  });
}


function mergeRegionCatalogs(catalog1Url, catalog1Format, catalog2Url, catalog2Format, outputFormat, callback) {
  $.ajax({
    cache: false,
    url:'js9Utils/movePropertyFromRegionCatalogToRegionCatalog.php',
    type:'get',
    data: {
      "catalog1Url" : catalog1Url,
      "catalog1Format" : catalog1Format,
      "catalog2Url" : catalog2Url,
      "catalog2Format" : catalog2Format,
      "outputFormat" : outputFormat
    },
    contentType: "application/json; charset=utf-8",
    async: true

  }).done(function(newCatalogUrl) {
      callback(newCatalogUrl);
  });
}


/**

*/
var scales = ["linear","log","histeq","power","sqrt","squared","asinh","sinh"];
var scaleIndex = 0;
var intervalId;

function scaleChangeIntervalTrigger(displayId){
  intervalId = window.setInterval(function(displayId){
    var currentScale = JS9.GetScale();
    JS9.SetScale(scales[scaleIndex], currentScale.scalemin, currentScale.scalemax);
    $('#JS9UtilsScaleBox').text("Color scale: "+scales[scaleIndex]).css({"font-size":"150%", "font-weight":"bold"});
    scaleIndex = (++scaleIndex)%(scales.length-1);
  }, 1500);
};

function stopScaleChangeTrigger(){
  window.clearInterval(intervalId);
}
