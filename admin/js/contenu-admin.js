( function ( $ ) {
	'use strict';

	angular.module( 'contenu', [ 'ui.sortable' ] ).config( [ '$httpProvider', '$logProvider', function ( $httpProvider, $logProvider ) {

		$logProvider.debugEnabled( true );

		$httpProvider.interceptors.push( [ function () {
			return {
				request: function ( config ) {
					config.headers = config.headers || {};
					//add nonce to avoid CSRF issues
					config.headers[ 'X-WP-Nonce' ] = contenu_ajax.nonce;

					return config;
				}
			};
		} ] );
	} ] );


	angular.module( 'contenu' ).directive( 'openDialog', function () {
		return {
			restrict: 'A',
			link: function ( scope, elem, attr, ctrl ) {

				var setUpDialog = function () {
					setTimeout( function () {
						$( '#dialog-confirm-' + scope.type.id ).dialog( {
							resizable: false,
							modal: true,
							autoOpen: false,
							buttons: {
								"Delete": function () {
									$( this ).dialog( "close" );
									scope.deleteType();
								},
								Cancel: function () {
									$( this ).dialog( "close" );
								}
							}
						} );
					}, 150 );
				};

				if ( scope.type.$$saved ) {
					setUpDialog();
				} else {
					var scope_watch = scope.$watch( 'type', function () {
						if ( scope.type.$$saved ) {
							setUpDialog();
							scope_watch();
						}
					}, true );
				}

				elem.bind( 'click', function ( e ) {
					$( '#dialog-confirm-' + scope.type.id ).dialog( 'open' );
				} );
			}
		};
	} );

	angular.module( 'contenu' ).controller( 'TypeBuilder', [ '$scope', '$http', function ( $scope, $http ) {

		$scope.types = [];
		$scope.selectedType = false;

		$scope.notice = {
			visible: false,
			content: '',
			style: ''
		};

		$scope.unCheckValues = function ( id ) {
			angular.forEach( $scope.selectedType.fields, function ( field ) {
				field.value = false;
			} );
		};

		$scope.setNotice = function ( content, styled ) {
			$scope.notice = {
				visible: true,
				content: content,
				style: 'notice-' + styled
			};
		}

		$scope.dismissNotice = function () {
			return $scope.notice.visible = false;
		};

		$scope.fieldSortableOptions = {
			handle: ".hndle",
			items: "div.postbox:not(.unsortable)"
		};

		$scope.sortableOptions = {
			handle: ".hndle",
			items: "div.postbox:not(.unsortable)",
			stop: function ( e, ui ) {

				var newOrder = [];

				$scope.types.forEach( function ( type ) {
					newOrder.push( type.id );
				} );

				$http.post( contenu_ajax.url + '?action=update_order', newOrder ).then( function ( res ) {

					if ( !res.data.success ) {
						$scope.setNotice( 'Problem updating the order.', 'error' )

					}

				} );
			},
		};

		$http.get( contenu_ajax.url, {
			params: {
				action: 'get_types'
			}
		} ).then( function ( res ) {
			if ( res.data.success ) {
				$scope.types = res.data.data;
				angular.forEach( $scope.types, function ( type ) {
					type.$$saved = true;
				} );
			} else {
				$scope.setNotice( res.data.data, 'error' );
			}

		} );

		$scope.addType = function () {
			$scope.types.push( {
				fields: [],
				single: false,
				$$saved: false
			} );
		};

		$scope.addField = function () {
			$scope.selectedType.fields.push( {
				type: 'text',
				private: false,
				value: false,
				width: 0,
				options: []
			} );
		};

		$scope.deleteField = function ( $index ) {
			$scope.selectedType.fields.splice( $index, 1 );
		};

		$scope.selectType = function ( id ) {
			angular.forEach( $scope.types, function ( type ) {
				type.$$collapsed = true;
				if ( type.id === id ) {
					$scope.selectedType = type;
				}
			} );
		};

	} ] );

	angular.module( 'contenu' ).controller( 'TypeBox', [ '$scope', '$http', function ( $scope, $http ) {

		if ( $scope.type ) {
			$scope.type.$$collapsed = true;
		}

		$scope.collapse = function () {
			$scope.selectType( $scope.type.id );
			$scope.type.$$collapsed = !$scope.type.$$collapsed;
		};

		var typeSaveTimeout = false;
		$scope.$watch( 'type', function ( n, o ) {
			if ( n && o && n !== o && $scope.type.$$saved ) {

				if ( typeSaveTimeout ) clearTimeout( typeSaveTimeout );

				typeSaveTimeout = setTimeout( function () {
					$scope.saveType();
				}, 1000 );

			}
		}, true );

		$scope.typeShortcode = function () {
			return '[datatable ' + $scope.type.id + '] or [datatable ' + $scope.type.name + ']';
		};

		$scope.typeName = function () {
			return ( $scope.type.name && $scope.type.name !== '' ) ? $scope.type.name : 'New Type';
		};

		$scope.saveType = function () {

			if ( !$scope.type.name || $scope.type.name === '' ) {

				$scope.setNotice( 'Please enter a name for the type before saving.', 'error' );

				return;
			}

			$http.post( contenu_ajax.url + '?action=save_type', $scope.type ).then( function ( res ) {
				if ( res.data.success ) {

					$scope.type.id = angular.copy( res.data.data.id );
					$scope.setNotice( 'Type saved successfully.', 'success' );
					$scope.type.$$saved = true;

				} else {

					$scope.setNotice( 'Type could not be saved successfully.', 'error' );

				}
			} );
		}


		$scope.deleteType = function () {

			$http.post( contenu_ajax.url + '?action=delete_type', {
				id: $scope.type.id
			} ).then( function ( res ) {

				if ( res.data.success ) {
					angular.forEach( $scope.types, function ( value, index ) {
						if ( value.id === $scope.type.id ) {
							$scope.types.splice( index, 1 );
						}
					} );
					$scope.selectedType = false;
				} else {
					$scope.setNotice( 'Unabled to delete type.', 'error' );

				}
			} );
		};


	} ] );

	angular.module( 'contenu' ).controller( 'FieldBox', [ '$scope', function ( $scope ) {

		$scope.collapsed = true;
		$scope.collapse = function () {
			$scope.collapsed = !$scope.collapsed;
		};

		$scope.fieldName = function () {
			return ( $scope.field.name && $scope.field.name !== '' ) ? $scope.field.name : 'New Field';
		};

		$scope.addOption = function () {
			$scope.field.options.push( {} );
		};

		$scope.removeOption = function ( $index ) {
			$scope.field.options.splice( $index, 1 );
		};

		$scope.hasOptions = function () {
			return ( $scope.field.type === 'select' || $scope.field.type === 'checkbox' || $scope.field.type === 'radio' || $scope.field.type === 'multicheckbox' || $scope.field.type === 'multicheckbox_inline' )
		};

		$scope.selectValue = function () {

      var value = angular.copy($scope.field.value);

			angular.forEach( $scope.selectedType.fields, function ( field ) {
				field.value = false;
			} );

			$scope.field.value = value;
		};
	} ] );

} )( jQuery );
