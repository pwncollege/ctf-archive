/**
 * OnShowImageInfo handler
 * 
 * Called when image mounted or selected
 * 
 * @param {String} imageId,
 * @param {Object} imageHash
 * @param {String} cache
 * @returns void
 */
var dtOnShowImageInfo = function(imageId, imageHash, cacheStr, status) {
	app.pageManager.switchPage(imageId, imageHash, status);
};

//var dtOnShowImageInfo = function(imageId, imageHash, cacheStr) {
//	app.pageManager.switchPageInTurn('8Mo2-8V1savu-59cEXV', 'ABCDEF123456891');
//};

/**
 * OnChangeConfig handler
 * 
 * @param {String} configJson
 * @returns void
 */
function dtOnChangeConfig(configJson) {
	var config = JSON.parse(configJson);
	MediaInfo.Config.languageAb = app.translation.getAbByLcid(config.language);
	MediaInfo.Config.anonymousStatisticsEnabled = config.anonymousStatisticsEnabled;
}
