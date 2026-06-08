export default function(e){return{getAnswer(e){return`
				<h4>${e?.heading??""}</h4>
				<p>${e?.text??""}</p>
				<span>${e?.footer??""}</span>
			`},getAnswerButtonsPre(){return""},addedAnswer(){},isWelcomeScreen(){return!0}}}