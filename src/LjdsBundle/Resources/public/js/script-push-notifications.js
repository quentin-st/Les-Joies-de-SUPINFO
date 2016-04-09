/**
 * Google Cloud Messaging push notifications
 */
(function() {
	var isPushEnabled = false,
		domItem = $('#push-notifications'),
		pushButton = $('#notifications-switch'),
		debug = domItem.data('debug'),
		worker = domItem.data('worker'),
		texts = {
			enable: 'Activer les notifications',
			disable: 'Désactiver les notifications'
		};

	$(window).load(function () {
		pushButton.click(function () {
			if (isPushEnabled)
				unsubscribe();
			else
				subscribe();
		});

		if ('serviceWorker' in navigator) {
			navigator.serviceWorker.register(worker).then(initialiseState).catch(function (error) {
				if (debug)
					console.log(error);
			});
		}
	});

	function initialiseState() {
		// Are Notifications supported in the service worker?
		if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
			if (debug)
				console.warn('Notifications aren\'t supported.');
			return;
		}

		// Check the current Notification permission.
		// If its denied, it's a permanent block until the
		// user changes the permission
		if (Notification.permission === 'denied') {
			if (debug)
				console.warn('The user has blocked notifications.');
			return;
		}

		// Check if push messaging is supported
		if (!('PushManager' in window)) {
			if (debug)
				console.warn('Push messaging isn\'t supported.');
			return;
		}

		// We need the service worker registration to check for a subscription
		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
			// Do we already have a push message subscription?
			serviceWorkerRegistration.pushManager.getSubscription()
				.then(function(subscription) {
					// Enable any UI which subscribes / unsubscribes from
					// push messages.
					pushButton.removeAttr('disabled');

					if (!subscription) {
						// We aren't subscribed to push, so set UI
						// to allow the user to enable push
						return;
					}

					// Keep your server in sync with the latest subscriptionId
					sendSubscriptionToServer(subscription);

					// Set your UI to show they have subscribed for
					// push messages
					pushButton.text(texts.disable);
					isPushEnabled = true;
				})
				.catch(function(err) {
					console.warn('Error during getSubscription()', err);
				});
		});
	}

	function sendSubscriptionToServer(subscription) {
		// Get registration id
		var endpoint = subscription.endpoint;
		var registrationId = endpoint.substr('https://android.googleapis.com/gcm/send/'.length);

		$.post(domItem.data('subscriptionuri'), {
			id: registrationId
		});
	}

	function sendUnsubscriptionToServer(registrationId) {
		$.ajax({
			url: domItem.data('unsubscriptionuri'),
			type: 'DELETE',
			data: {
				id: registrationId
			}
		});
	}

	function subscribe() {
		// Disable the button so it can't be changed while
		// we process the permission request
		pushButton.attr('disabled', 'disabled');

		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
			serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
				.then(function(subscription) {
					// The subscription was successful
					isPushEnabled = true;
					pushButton.text(texts.disable);
					pushButton.removeAttr('disabled');

					return sendSubscriptionToServer(subscription);
				})
				.catch(function(e) {
					if (Notification.permission === 'denied') {
						// The user denied the notification permission which
						// means we failed to subscribe and the user will need
						// to manually change the notification permission to
						// subscribe to push messages
						if (debug)
							console.warn('Permission for Notifications was denied');
						pushButton.disabled = true;
					} else {
						// A problem occurred with the subscription; common reasons
						// include network errors, and lacking gcm_sender_id and/or
						// gcm_user_visible_only in the manifest.
						if (debug)
							console.error('Unable to subscribe to push.', e);
						pushButton.attr('disabled', 'disabled');
						pushButton.text(texts.enable);
					}
				});
		});
	}

	function unsubscribe() {
		pushButton.removeAttr('disabled');

		navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
			// To unsubscribe from push messaging, you need get the  
			// subscription object, which you can call unsubscribe() on.  
			serviceWorkerRegistration.pushManager.getSubscription().then(
				function(pushSubscription) {
					// Check we have a subscription to unsubscribe  
					if (!pushSubscription) {
						// No subscription object, so set the state  
						// to allow the user to subscribe to push  
						isPushEnabled = false;
						pushButton.disabled = false;
						pushButton.textContent = texts.enable;
						return;
					}

					var subscriptionId = pushSubscription.subscriptionId;
					sendUnsubscriptionToServer(subscriptionId);

					// We have a subscription, so call unsubscribe on it  
					pushSubscription.unsubscribe().then(function(successful) {
						pushButton.disabled = false;
						pushButton.textContent = texts.enable;
						isPushEnabled = false;
					}).catch(function(e) {
						// We failed to unsubscribe, this can lead to  
						// an unusual state, so may be best to remove
						// the users data from your data store and
						// inform the user that you have done so

						if (debug)
							console.log('Unsubscription error: ', e);
						pushButton.disabled = false;
						pushButton.textContent = texts.enable;
					});
				}
			).catch(function(e) {
				if (debug)
					console.error('Error thrown while unsubscribing from push messaging.', e);
			});
		});
	}
})();
