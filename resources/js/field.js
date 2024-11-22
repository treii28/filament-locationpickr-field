import { Loader } from '@googlemaps/js-api-loader'

export default function locationPickrField({ location, config }) {
    return {
        map: null,
        marker: null,
        overlay: null,
        markerLocation: null,
        infoWindow: null,
        loader: null,
        location: null,
        config: {
            draggable: true,
            clickable: false,
            defaultZoom: 12.8,
            latLngBounds: {
                north: 45.21,
                south: 45.11,
                west: -84.2,
                east: -84.29,
            },
            controls: {
                mapTypeControl: true,
                scaleControl: true,
                streetViewControl: false,
                rotateControl: false,
                fullscreenControl: false,
                zoomControl: true,
            },
            myLocationButtonLabel: '',
            defaultLocation: {
                lat: 45.158,
                lng: -84.245,
            },
            apiKey: '',
            statePath: '',
            overlayConfig: {
                overlayUrl: null,
                overlayBounds: {
                    north: 45.21,
                    east: -84.2,
                    south: 45.11,
                    west: -84.29
                },
                overlayOpacity: 0.25
            }
        },

        init: function () {
            this.location = location
            this.config = { ...this.config, ...config }
            this.loadGmaps()
            this.$watch('location', (value) => this.updateMapFromAlpine())
        },

        loadGmaps: function () {
            this.loader = new Loader({
                apiKey: this.config.apiKey,
                version: 'weekly',
            })

            this.loader
                .load()
                .then((google) => {
                    class GmapOverlay extends google.maps.overlayView {
                        constructor(bounds, image, opacity) {
                            super();
                            this.bounds = bounds;
                            this.image = image;
                            this.opacity = opacity;
                            this.div = null;
                        }

                        onAdd() {
                            this.div = document.createElement("div");
                            this.div.style.borderStyle = "none";
                            this.div.style.borderWidth = "0px";
                            this.div.style.position = "absolute";
                            this.div.style.pointerEvents = "none";

                            const img = document.createElement("img");
                            img.src = this.image;
                            for (const [key, value] of Object.entries(window.overlayStyle))
                                img.style[key] = value
                            //img.style = window.overlayStyle;
                            this.div.appendChild(img);

                            const panes = this.getPanes();
                            panes.overlayLayer.appendChild(this.div);
                        }

                        draw() {
                            const overlayProjection = this.getProjection();
                            const sw = overlayProjection.fromLatLngToDivPixel(this.bounds.getSouthWest());
                            const ne = overlayProjection.fromLatLngToDivPixel(this.bounds.getNorthEast());

                            if (this.div) {
                                this.div.style.left = sw.x + "px";
                                this.div.style.top = ne.y + "px";
                                this.div.style.width = ne.x - sw.x + "px";
                                this.div.style.height = sw.y - ne.y + "px";
                            }
                        }

                        onRemove() {
                            if (this.div) {
                                this.div.parentNode.removeChild(this.div);
                                delete this.div;
                            }
                        }
                    }

                    this.map = new google.maps.Map(this.$refs.map, {
                        center: this.getCoordinates(),
                        zoom: this.config.defaultZoom,
                        ...this.config.controls,
                    })

                    this.infoWindow = new google.maps.InfoWindow()

                    this.marker = new google.maps.Marker({
                        draggable: this.config.draggable,
                        map: this.map,
                    })
                    this.marker.setPosition(this.getCoordinates())
                    this.setCoordinates(this.marker.getPosition())

                    if (this.config.clickable) {
                        this.map.addListener('click', (event) => {
                            this.markerMoved(event)
                        })
                    }

                    if (this.config.draggable) {
                        google.maps.event.addListener(
                            this.marker,
                            'dragend',
                            (event) => {
                                this.markerMoved(event)
                            },
                        )
                    }

                    const locationButtonDiv = document.createElement('div')
                    locationButtonDiv.classList.add('location-div')
                    locationButtonDiv.appendChild(this.createLocationButton())
                    this.map.controls[
                        google.maps.ControlPosition.TOP_LEFT
                        ].push(locationButtonDiv)

                    let mapBounds = this.getMapBounds()
                    if(mapBounds !== null) {
                        this.map.setOptions({
                            restriction: {
                                latLngBounds: mapBounds,
                                strictBounds: false
                            }
                        })
                    }

                    let overlayConfig = this.getOverlayConfig()
                    if(overlayConfig !== null) {
                        this.overlay = new GmapOverlay(overlayConfig.overlayBounds, overlayConfig.overlayUrl, overlayConfig.overlayOpacity)
                        this.overlay.setMap(this.map);
                    }

                })
                .catch((error) => {
                    console.error('Error loading Google Maps API:', error)
                })
        },

        getMapBounds: function() {
            return (this.config.latLngBounds.length > 0) ? this.config.latLngBounds : null;
        },

        getOverlayConfig: function() {
            if(this.config.overlayConfig.hasOwnProperty('overlayUrl') && this.config.overlayConfig.overlayUrl !== null) {
                let overlayCfg = {
                    overlayUrl: this.config.overlayConfig.overlayUrl
                }

                if(this.config.overlayConfig.hasOwnProperty('overlayBounds'))
                    overlayCfg.overlayBounds = this.config.overlayConfig.overlayBounds
                if(this.config.overlayConfig.hasOwnProperty('overlayOpacity'))
                    overlayCfg.overlayOpacity = this.config.overlayConfig.overlayOpacity

                return overlayCfg
            } else
                return null
        },

        createLocationButton: function () {
            const locationButton = document.createElement('button')
            locationButton.type = 'button'
            locationButton.textContent = this.config.myLocationButtonLabel
            locationButton.classList.add('location-button')
            locationButton.addEventListener('click', (event) => {
                event.preventDefault()
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.markerLocation = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            }
                            this.setCoordinates(this.markerLocation)
                            this.marker.setPosition(this.markerLocation)
                            this.map.panTo(this.markerLocation)
                        },
                        () => {
                            this.myLocationError(
                                true,
                                this.infoWindow,
                                this.map.getCenter(),
                            )
                        },
                    )
                } else {
                    this.myLocationError(
                        false,
                        this.infoWindow,
                        this.map.getCenter(),
                    )
                }
            })

            return locationButton
        },

        markerMoved: function (event) {
            this.markerLocation = event.latLng.toJSON()
            this.setCoordinates(this.markerLocation)
            this.marker.setPosition(this.markerLocation)
            this.map.panTo(this.markerLocation)
        },

        updateMapFromAlpine: function () {
            const location = this.getCoordinates()
            const markerLocation = this.marker.getPosition()
            if (
                !(
                    location.lat === markerLocation.lat() &&
                    location.lng === markerLocation.lng()
                )
            ) {
                this.updateMap(location)
            }
        },

        updateMap: function (position) {
            this.marker.setPosition(position)
            this.map.panTo(position)
        },

        setCoordinates: function (position) {
            this.$wire.set(this.config.statePath, position)
        },

        getCoordinates: function () {
            let location = this.$wire.get(this.config.statePath)
            if (
                location === null ||
                !location.hasOwnProperty('lat') ||
                !location.hasOwnProperty('lng')
            ) {
                location = {
                    lat: this.config.defaultLocation.lat,
                    lng: this.config.defaultLocation.lng,
                }
            }

            return location
        },

        myLocationError: function (browserHasGeolocation, infoWindow, pos) {
            infoWindow.setPosition(pos)
            infoWindow.setContent(
                browserHasGeolocation
                    ? 'Error: The Geolocation service failed.'
                    : "Error: Your browser doesn't support geolocation.",
            )
            infoWindow.open(this.map)
        },
    }
}
