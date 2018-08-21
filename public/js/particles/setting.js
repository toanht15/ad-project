particlesJS('particle',{
        "particles":{
            "number":{
                "value":50,
                "density":{
                    "enable":true,
                    "value_area":300
                }
            },
            "shape":{
                "type":"circle",
                "stroke":{
                    "width":0,
                    "color":"#ffcc00"
                },
                "polygon": {
                    "nb_sides": 5
                },
                "image": {
                    "src": "images/hoge.png",
                    "width": 100,
                    "height": 100
                }
            },
            "color":{
                "value":"#ccc"
            },
            "opacity":{
                "value":0.7,
                "random":false,
                "anim":{
                    "enable":false,
                    "speed":10,
                    "opacity_min":0.1,
                    "sync":false
                }
            },
            "size":{
                "value":5,
                "random":true,
                "anim":{
                    "enable":false,
                    "speed":40,
                    "size_min":0.1,
                    "sync":false
                }
            },
            "line_linked":{
                "enable":true,
                "distance":150,
                "color":"#ccc",
                "opacity":0.4,
                "width":1
            },
            "move":{
                "speed":2,
                "straight":false,
                "direction":"none",
                "out_mode":"out"
            }
        },

        "interactivity":{
            "detect_on":"canvas",
            "events":{
                "onhover":{
                    "enable":false,
                    "mode":"repulse"
                },
                "onclick":{
                    "enable":true,
                    "mode":"push"
                }
            },

            "modes":{
                "grab":{
                    "distance":400,
                    "line_linked":{
                        "opacity":1
                    }
                },

                "repulse":{
                    "distance":200
                },

                "bubble":{
                    "distance":400,
                    "size":40,
                    "opacity":8,
                    "duration":2,
                    "speed":3
                },
                "push":{
                    "particles_nb":4
                },
                "remove":{
                    "particles_nb":2
                }
            }
        },
        "retina_detect":true,
        "resize":true
    }
);