'use strict';

define([
    'uploader',
    'ng-infinite-scroll'
], function() {

    angular.module('Component.Gallery.Admin', ['infinite-scroll']).
        config(['$routeProvider', function($routeProvider, $locationProvider) {
            $routeProvider
                .when('/gallery', {
                    controller: 'GalleryCtrl',
                    templateUrl: '/Components/Gallery/views/admin/album.html',
                    reloadOnSearch: false
                });
        }])
        .run(function(dashboard) {
            // add item to admin main menu
            dashboard.mainMenu.push({
                component: 'Gallery',
                url: '#!/gallery',
                title: 'Gallery',
                icon: 'icon-picture'
            });
        })
        .directive('ngSortable', function() {
            return {
                require: '?ngModel',
                link: function(scope, element, attrs, ngModel) {
                    var onStart, onUpdate, opts, _start, _update;
                    opts = angular.extend({}, scope.$eval(attrs.ngSortable));
                    if (ngModel != null) {
                        onStart = function(e, ui) {
                            return ui.item.data('ui-sortable-start', ui.item.index());
                        };
                        onUpdate = function(e, ui) {
                            var end, start;
                            start = ui.item.data('ui-sortable-start');
                            end = ui.item.index();
                            ngModel.$modelValue.splice(end, 0, ngModel.$modelValue.splice(start, 1)[0]);
                            return scope.$apply();
                        };
                        _start = opts.start;
                        opts.start = function(e, ui) {
                            onStart(e, ui);
                            if (typeof _start === "function") {
                                _start(e, ui);
                            }
                            return scope.$apply();
                        };
                        _update = opts.update;
                        opts.update = function(e, ui) {
                            onUpdate(e, ui);
                            if (typeof _update === "function") {
                                _update(e, ui);
                            }
                            return scope.$apply();
                        };
                    }
                    return element.sortable(opts);
                }
            };
        })
    .factory('AlbumService', function($resource) {
            return $resource('/rest.php/gallery/:id/:photo_id', { 'id': '@' }, {
                create: { method: 'PUT' },
                savePhoto: { method: 'POST', params: { 'photo_id': '@id', 'id': '@album_id' }, isArray: false },
                updateOrder: { method: 'PUT', data: { 'orders': '@' }, isArray: false }
            });
    })
    .controller('GalleryCtrl', function($scope, $rootScope, $location, $routeParams, AlbumService, $http) {
        $rootScope.breadcrumbs = [
            {
                'title' : 'Dashboard',
                'url': '#!/'
            },
            {
                'title' : 'Gallery'
            }
        ];

        // open album for adding images
        $scope.showAlbum = function(album) {
            $location.search({ id: album.id });
        }
        var getById = function(id) {
            var ret = null;
            angular.forEach($scope.albums, function(album) {
                if (album.id == id) ret = album;
            });
            return ret;
        }
        // load list of albums
        $scope.updateAlbums = function(album) {
            return AlbumService.query(function(albums) {
                $scope.albums = albums;
                $scope.album = getById($routeParams.id);
            });
        }
        // create album
        $scope.createAlbum = function(album) {
            if ($scope.newAlbum.$invalid) {
                return;
            }
            album.busy = true;
            AlbumService.create(album, function(album) {
                $scope.nAlbum = { busy: false };
                $scope.albums.push(album);
                $location.search({ id: album.id });
            });
        }
        $scope.updateAlbums(); // load albumn on start

        // Delete Album
        $scope.deleteAlbum = function(album) {
            AlbumService.delete({ id: album.id }, function() {
                angular.forEach($scope.albums, function(item, key) {
                    if (item.id == album.id) {
                        $scope.albums.splice(key, 1);
                    }
                });
            });
        }
        // Delete photo
        $scope.deletePhoto = function(photo) {
            var req = photo.$delete({ id: photo.album_id, photo_id: photo.id }, function() {
                $scope.album.images_count--;
                angular.forEach($scope.photos, function(item, key) {
                    if (item.id == photo.id) {
                        $scope.photos.splice(key, 1);
                    }
                });
            });
        }
        // Edit photo
        $scope.editPhoto = function(photo) {
            $scope.photo = photo;
            $location.search({ id: photo.album_id, photo_id: photo.id });
        }
        // Edit photo
        $scope.savePhoto = function(photo) {
            photo.$savePhoto({ id: photo.album_id, photo_id: photo.id }, function() {
                $location.url($location.url());
            });
        }

        $scope.$on('$locationChangeSuccess', function(event) {
            $scope.albumId = $routeParams.id;
            $scope.photoId = $routeParams.photo_id;
            $scope.album = getById($routeParams.id);

            if ($scope.album) {
                //  $scope.photo = PhotoService.get({ album_id: $routeParams.id, id: $routeParams.photoId });
                $scope.busy = true;
                AlbumService.query({ id: $scope.album.id, page: 1 }, function(result) {
                    $scope.page = 1;
                    $scope.busy = false;
                    $scope.photos = result;
                });

                $rootScope.breadcrumbs = [
                    {
                        'title' : 'Dashboard',
                        'url': '#!/'
                    },
                    {
                        'title' : 'Gallery',
                        'url': '#!/gallery'
                    },
                    {
                        'title' : $scope.album.title.ukr
                    }
                ];
            }
        });

        $scope.addImage = function(album, file) {
            if (angular.isUndefined($scope.photos)) {
                $scope.photos = [];
            }
            if (album && file) {
                album.images_count++;
                console.info(file);
                $scope.photos.unshift(file);
            }
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        }

        $scope.$watch('photoId', function(value) {
            // show edit photo modal window
            if (typeof value != 'undefined') {
                var photo = null;
                angular.forEach($scope.photos, function(item) {
                    if (item.id == value) {
                        photo = item;
                    }
                });
                if (photo != null) {
                    $scope.editPhoto(photo);
                    return;
                }
            }
        });

        $scope.busy = false;
        $scope.page = 0;
        $scope.nextPage = function() {
            if ($scope.busy) return;
            $scope.busy = true;
            if ($scope.page == 0) {
                $scope.photos = [];
            }

            AlbumService.query({ id: $routeParams.id, page: ++$scope.page }, function(photos) {
                if (photos.length == 0) {
                    $scope.page--;
                }
                angular.forEach(photos, function(item) {
                    $scope.photos.push(item);
                });
                $scope.photoId = $routeParams.photo_id;
                $scope.busy = false;
            });
        };

        $scope.update = function(e, ui) {
            var orders = $scope.photos.map(function(item){ return item.id; });
            $scope.busy = true;
            AlbumService.updateOrder({ id: $routeParams.id }, { orders: orders }, function(result) {
                angular.forEach($scope.photos, function(item) {
                    if (typeof result[item.id] != 'undefined') {
                        item.order = result[item.id];
                    }
                });
                $scope.busy = false;
            });
        };
    });

});