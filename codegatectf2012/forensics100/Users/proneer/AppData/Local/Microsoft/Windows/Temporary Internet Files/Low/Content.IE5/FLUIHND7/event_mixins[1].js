/**
  *  Event Mixins
  *  (c) 2006 Seth Dillingham <seth.dillingham@gmail.com>
  *
  *  This software is hereby released into the public domain. Do with it as
  *  you please, but with the understanding that it is provided "AS IS" and 
  *  without any warranty of any kind.
  *  
  *  (But I'd love to be told about where and how this code is being used.)
  **/
  
/**
  *  Description:
  *    add support (to any object or class) by mixing this class into your own
  *  
  *  Requires prototype.js
  *  
  *  Usage:
  *    To publish custom events:
  *      1. mix this class with your own via
  *         Object.extend( [your class or prototype], Event.Publisher )
  *      2. post events by calling
  *         this.dispatchEvent( [event name], [data for event] )
  *   
  *    To activate and deactivate the event-tracing feature, just call 
  *      this.toggleEventsTrace()
  **/

Event.Publisher = Class.create();
Object.extend( Event.Publisher, {
	_ls_event_targets: null,
	
	_event_source_id: null,
	
	_fl_trace_events: false,
	
	getEventSourceId: function() {
		if ( typeof this._event_source_id == 'function' )
			return this._event_source_id();
		else
			return this._event_source_id;
	},
	
	getEventTarget: function( event_name ) {
		if ( ! this._ls_event_targets )
			this._ls_event_targets = new Array();
		
		if ( ! this._ls_event_targets[ event_name ] )
			document.body.appendChild(
				this._ls_event_targets[ event_name ] = document.createElement( 'A' )
			);
		
		return this._ls_event_targets[ event_name ];
	},
	
	addEventListener: function( event_name, callback_func, capturing ) {
		var targ = this.getEventTarget( event_name );
		
		Event.observe( targ, 'click', callback_func, capturing );
		
		if ( this._fl_trace_events ) {
			var data =  {
				publisher: this.getEventSourceId(),
				event_name: event_name,
				listener: callback_func,
				capturing: capturing,
				event_source_proxy: targ
			};
			
			this.dispatchEvent( 'eventListenerAdded', data, true, true );
		}
	},
	
	removeEventListener: function( event_name, callback_func, capturing ) {
		var targ = this.getEventTarget( event_name );
		
		Event.stopObserving( targ, 'click', callback_func, capturing );
		
		if ( this._fl_trace_events ) {
			var data =  {
				publisher: this.getEventSourceId(),
				event_name: event_name,
				listener: callback_func,
				capturing: capturing,
				event_source_proxy: targ
			};
			
			this.dispatchEvent( 'eventListenerRemoved', data, true, true );
		}
	},
	
	dispatchEvent: function( event_name, data, can_bubble, cancelable ) {
		var targ = this.getEventTarget( event_name );
		var event_data = {
			event_name: event_name,
			event_target: this,
			data: data ? data : null
		};
		
		if ( ! can_bubble ) can_bubble = false;
		if ( ! cancelable ) cancelable = false;
		
		var event = Event.create( event_data, can_bubble, cancelable, true, targ );
		
		if ( this._fl_trace_events ) {
			if ( event_name.match( /event(?:ListenerAdded|ListenerRemoved|Dispatched|Received)/ ) )
				return;
			
			var data =  {
				publisher: this.getEventSourceId(),
				event_name: event_name,
				event_data: event_data,
				can_bubble: can_bubble,
				cancelable: cancelable,
				event_source_proxy: targ,
				result: event
			};
			
			this.dispatchEvent( 'eventDispatched', data, true, true );
		}
	},
	
	toggleEventsTrace: function() {
		var trace = Event.Tracer.findTracer();
		
		if ( ! trace || ! this._fl_trace_events ) {
			this._fl_trace_events = true;
			
			trace = Event.Tracer.startTrace();
			
			trace.registerPublisher( this );
		}
		else {
			this._fl_trace_events = false;
			
			if ( trace )
				trace.unregisterPublisher( this );
		}
		
		return this._fl_trace_events;
	},
	
	isEventsTraceActive: function() {
		return this._fl_trace_events;
	}
} );

/**
  *  MIX IN: Event.Listener
  *  
  *  Description:
  *    easily add support for receiving totally custom events
  *    (to any object or class) by mixing this class into
  *    your own
  *  
  *  Usage:
  *	   To receive custom events:
  *      1. mix this class with your own via
  *         Object.extend( [your class or prototype], EventListener )
  *      2. listen for events by calling (from your object)
  *         this.listen()
  *         (see params for this.listen, below)
  **/
Event.Listener = Class.create();
Object.extend( Event.Listener,
{
	_listens: null,
	
	getEventHandlerName: function( event_name ) {
		var onEvent_name = event_name.split( /[ _]/ ).join( '-' ).camelize();
		
		return "on" + onEvent_name.charAt( 0 ).toUpperCase() + onEvent_name.substr( 1 );
	},
	
	/**
	  *	 Params:
      *    event_source [object]:
      *      the object which will generate the events, and which implements (or
      *      mixes in) the Event.Publisher interface (we need addEventListener)
      *    event_name [string]:
      *      the name of the event for which your object will listen
      *    use_capture [boolean]:
      *      standard DOM Event API param
      *    onEvent_name [string]:
      *      the name of the method in your object which will be called when the
      *      event is received if you omit this param, listen will look for a
      *      function named with the CapitalizedCamelCased name of the event with
      *      "on" at the front. So, if the event is named "message_received",
      *      we'll look for a function named "onMessageReceived" You can override
      *      this behavior by overriding getEventHandlerName in your object.
	  **/
	listenForEvent: function( event_source, event_name, use_capture, onEvent_name ) {
		if ( ! onEvent_name )
			onEvent_name = this.getEventHandlerName( event_name );
		
		if ( ! this._listens ) this._listens = new Array();
		
		//added this in to allow for anonymous function handling of an event
		var eventHandler = this[onEvent_name];
		
		if(typeof(onEvent_name) == 'function') {
			eventHandler = onEvent_name;
		}
		
		var cb = eventHandler.bindAsEventListener( this );
		this._listens.push( [ event_source, event_name, use_capture, onEvent_name, cb ] )
		
		event_source.addEventListener( event_name, cb, use_capture );
	},
	
	stopListeningForEvent: function( event_source, event_name, use_capture, onEvent_name ) {
		if ( ! this._listens ) return false;
		
		if ( ! onEvent_name )
			onEvent_name = this.getEventHandlerName( event_name );
		
		var ix_item = -1;
		var ls = this._listens.detect( function( val, ix ) {
			if ( ( val[ 0 ] == event_source )
			  && ( val[ 1 ] == event_name )
			  && ( val[ 2 ] == use_capture )
			  && ( val[ 3 ] == onEvent_name ) ) {
				ix_item = ix;
				return true;
			}
		} );
		
		if ( ix_item >= 0 ) {
			this._listens.splice( ix_item, 1 );
			
			event_source.removeEventListener( event_name, ls[ 4 ], use_capture );
			
			return true;
		}
		
		return false;
	}
} );

/**
  *  Extensions to Prototype's Event object,
  *  for cleanly creating and dispatching custom events
  *  
  *  Called from Event.Publisher
  **/
Object.extend( Event,
{
	create: function( event_data, can_bubble, cancelable, fl_dispatch, target ) {
		var event;
		
		if ( document.createEvent ) {  // gecko, safari
			if ( ! can_bubble ) can_bubble = false;
			if ( ! cancelable ) cancelable = false;
			
			if ( /Konqueror|Safari|KHTML/.test( navigator.userAgent ) ) {
				event = document.createEvent( 'HTMLEvents' )
				
				event.initEvent( 'click', can_bubble, cancelable );
			}
			else {  // gecko uses MouseEvents
				event = document.createEvent( 'MouseEvents' )
				
				event.initMouseEvent( "click", can_bubble, cancelable,
				                      window, 0, 0, 0, 0, 0,
				                      false, false, false, false, 0, null );
			}
		}
		else {  // msie
			event = document.createEventObject();
			event.event_type = 'onclick';
		}
		
		event.event_data = event_data;
		
		if ( fl_dispatch )
			Event.dispatch( target, event );
		
		return event;
	},
	
	dispatch: function( target, event ) {
		if ( document.createEvent )
			return target.dispatchEvent( event );
		else
			return target.fireEvent( ( typeof( event.event_type ) != "undefined" ) ? event.event_type : 'onclick', event );
	}
} );


