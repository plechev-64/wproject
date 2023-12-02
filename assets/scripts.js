
let BaseRoute = '/wp-json/don/v2';

let ObjectManagerList = {
	list: [],
	add: function(managerProps){
		let manager = new ObjectManager(managerProps);
		this.list.push(manager);
		return manager;
	},
	addIfNotExist: function(managerProps){
		let manager = this.getByWrapper(managerProps.wrapper)
		if(manager === null){
			manager = this.add(managerProps);
		}
		return manager;
	},
	getByWrapper: function(wrapper){
		let manager = null;
		this.list.forEach(function(ObjectManager){
			if(ObjectManager.wrapper === wrapper){
				manager = ObjectManager;
			}
		});
		return manager;
	},
};

class ObjectManager {

	constructor(props) {
		this.ended = false;
		this.wrapper = props.wrapper;
		this.route = props.route;
		this.setupPageObject(props);
	}

	setupPageObject (props) {
		this.page = props.page;
		this.number = props.number;
		this.filters = props.filters;
		this.queue = props.queue;
		this.isShuffle = props.isShuffle;
		this.sort = props.sort;
		this.args = props.args;
	}

	getSenderObject () {
		return {
			page: this.page,
			number: this.number,
			filters: this.filters,
			queue: this.queue,
			isShuffle: this.isShuffle,
			sort: this.sort,
			args: this.args,
		};
	}

	cancelPager() {
		this.page = 1;
		this.ended = false;
	}

	getProp (propName) {
		return JSON.parse(this[propName]);
	}

	setProp (propName, value) {
		this[propName] = JSON.stringify(value);
	}

	load (button) {
		if (!Ajax.isWaiting && !this.ended) {
			button.classList.add("load");
			let self = this;
			self.page++;
			this.send(function (response) {
				button.classList.remove("load");
				if (self.ended || (response.length && response.length < self.number)) {
					button.remove();
				}
			},true);
		}
	}

	initLazyLoad () {
		let self = this;
		let loadMore = jQuery('.object-load-more[data-wrapper="'+self.wrapper+'"]');
		jQuery(window).scroll(function () {
			if (elementScrolled(loadMore) && !Ajax.isWaiting && !self.ended) {
				self.page++;
				loadMore.html('<img class="loader-more" src="/wp-content/themes/don/images/loader.gif">')
				self.send(function (response) {
					loadMore.html('');
				},true);
			}
		});
	}

	send (callback, isShort) {
		let self = this;
		if(self.number < 0){
			self.ended = true;
		}
		let ajax = isShort? AjaxShort: Ajax;
		let wrapper = document.querySelector(self.wrapper);
		wrapper.classList.add('list-loading');
		ajax.send(
			BaseRoute + self.route,
			self.getSenderObject(),
			function (response) {
				if (!response.statusCode || response.statusCode !== 200) {
					self.ended = true;
				} else {
					if (response.managerData) {
						for (let propName in response.managerData) {
							self.setProp(propName, response.managerData[propName]);
						}
					}
					wrapper.insertAdjacentHTML('beforeend', response.result);
					wrapper.classList.remove('list-loading');
				}
				if (callback) {
					callback(response);
				}
			}
		);
	}
}

let Ajax = {
    ended: false,
    isWaiting: false,
    nonce: '',
    url: '',
    post: function( args, callbackResponse ) {
        this.send( this.url, args, callbackResponse );
    },
    send: function( url, args, callbackResponse ) {

        let formData;
        if ( Array.isArray( args ) ) {
            formData = args[1];
            args = args[0];
        } else {
            formData = new FormData();
        }

        formData.append( '_wpnonce', wpApiSettings.nonce );

        for ( let key in args ) {
            formData.append( key, args[key] );
        }

        this.isWaiting = true;

        fetch( url, {
            method: 'POST',
            body: formData,
        } ).then( function( response ) {
            response.json().then( function( result ) {
                Ajax.isWaiting = false;
                if ( callbackResponse ) {
                    callbackResponse( result );
                }
                lazyObserver.observe();
            } );
        } );

    },
};

let AjaxShort = {
    send: function( route, args, callbackResponse ){
        Ajax.send(
            '/api/index.php?route='+route,
            args,
            callbackResponse
        );
    }
}
