var WPFormsAIFormGenerator=window.WPFormsAIFormGenerator||((e,r,s)=>{let l=wpforms_ai_form_generator,o={state:{},main:null,preview:null,isFormBuilderReady:!1,init(){r.wpforms_builder?.is_ai_disabled||o.isLoaded||(o.updateLocationUrl(),o.events(),o.isLoaded=!0)},events(){s(e).on("wpformsSetupPanelBeforeInitTemplatesList",o.addTemplateCard),s("#wpforms-builder").on("wpformsBuilderReady",o.maybeSaveForm).on("wpformsBuilderPanelLoaded",o.panelLoaded)},panelLoaded(e,a){"setup"===a&&Promise.all([import(l.modules.main),import(l.modules.preview),import(l.modules.modals)]).then(([e,a,t])=>{o.main=e.default(o,s),o.preview=a.default(o,s),o.modals=t.default(o,s),o.main.init()})},addTemplateCard(){s("#wpforms-template-generate").length||(s("#wpforms-setup-templates-list .list").prepend(o.renderTemplateCard()),wpf.initTooltips(s("#wpforms-template-generate .wpforms-template-buttons")))},renderTemplateCard(){var e="generate"===r.wpforms_builder?.template_slug?"selected":"";let a="",t=!Object.keys(l.addonsData).length||l.dismissed.installAddons?"wpforms-template-generate":"wpforms-template-generate-install-addons";return l.isPro||l.liteConnectAllowed||(t+=" wpforms-inactive wpforms-help-tooltip wpforms-prevent-default",a=`data-tooltip-position="top" title="${l.templateCard.liteConnectNotAllowed}"`),l.isPro||l.liteConnectEnabled||!l.liteConnectAllowed||(t+=" enable-lite-connect-modal wpforms-prevent-default"),`
				<div class="wpforms-template ${e}" id="wpforms-template-generate">
					<div class="wpforms-template-thumbnail">
						<div class="wpforms-template-thumbnail-placeholder">
							<img src="${l.templateCard.imageSrc}" alt="${l.templateCard.name}" loading="lazy">
						</div>
					</div>
					<div class="wpforms-template-name-wrap">
						<h3 class="wpforms-template-name categories has-access favorite slug subcategories fields" data-categories="all,new" data-subcategories="" data-fields="" data-has-access="1" data-favorite="" data-slug="generate">
							${l.templateCard.name}
						</h3>
						<span class="wpforms-badge wpforms-badge-sm wpforms-badge-inline wpforms-badge-purple wpforms-badge-rounded">${l.templateCard.new}</span>
					</div>
					<p class="wpforms-template-desc">
						${l.templateCard.desc}
					</p>
					<div class="wpforms-template-buttons">
						<a href="#" class="${t} wpforms-btn wpforms-btn-md wpforms-btn-purple-dark" ${a}>
							${l.templateCard.buttonTextInit}
						</a>
					</div>
				</div>
			`},maybeSaveForm(){o.isFormBuilderReady=!0,wpforms_ai_chat_element.forms?.chatHtml&&!wpf.getQueryString("newform")&&WPFormsBuilder.formSave(!1)},updateLocationUrl(){history.replaceState({},null,wpf.updateQueryString("session",null))}};return o})(document,window,jQuery);WPFormsAIFormGenerator.init();