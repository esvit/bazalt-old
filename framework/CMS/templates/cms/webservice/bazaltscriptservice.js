/**
 * Base script for Webservices
 */
var BAZALTScriptService = {
    /**
     * Maximum count of parallel requests
     */
    maxConnections: 3,

    /**
     * Repeat check available connection time
     */
    waitDelay: 300,

    /**
     * Connections
     */
    connections: [],

    handlers: {
        pre: {},
        post: {}
    },

    escapeable: /["\\\x00-\x1f\x7f-\x9f]/g,

    meta: {
        '\b': '\\b',
        '\t': '\\t',
        '\n': '\\n',
        '\f': '\\f',
        '\r': '\\r',
        '"' : '\\"',
        '\\': '\\\\'
    },

    /**
     * Create request object
     */
    remoteConnection: function() {
        this.busy = false;
        if (window.XMLHttpRequest) {
            this.request = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            this.request = new ActiveXObject('Msxml2.XMLHTTP');
            if (!this.request) {
                this.request = new ActiveXObject('Microsoft.XMLHTTP');
            }
        }
    },

    /**
     * Get available connection
     */
    getAvailableConnection: function() {
        for (var i = 0; i < this.maxConnections; i++) {
            if (!this.connections[i]) {
                var conn = new this.remoteConnection();

                this.connections[i] = conn;

                return conn;
            } else if (!this.connections[i].busy) {
                this.connections[i] = new this.remoteConnection();
                return this.connections[i];
            }
        }

        return null;
    },

    onBeforeSend: function(method, args) {
    },

    onAfterRecive: function(result, context, method) {
    },

    onSuccess: function(result, context, method) {
    },
    
    onFailure: function(result, context, method) {
    },

    addHandler: function(handlerType, servicePath, handlerName, callback) {
        if (!(/pre|post/i.test( handlerType ))) {
            throw Error('Unknown handler type '+handlerType);
        }
        if(this.handlers[handlerType][servicePath] == undefined) {
            this.handlers[handlerType][servicePath] = {};
        }
        this.handlers[handlerType][servicePath][handlerName] = callback;
    },

    removeHandler: function(handlerType, servicePath, handlerName) {
        delete this.handlers[handlerType][servicePath][handlerName];
    },

    callMethod: function(servicePath, serviceFormat, methodName, args, onSuccess, onFailure, context) {
        var self = this;
        var connection = this.getAvailableConnection();

        if (onSuccess == undefined) {
            onSuccess = this.onSuccess;
        }
        if (onFailure == undefined) {
            onFailure = this.onFailure;
        }

        if (!connection) {
            setTimeout(function() { 
                self.callMethod(servicePath, serviceFormat, methodName, args, onSuccess, onFailure, context);
            }, this.waitDelay);
        } else {
            connection.busy = true;
            connection.servicePath = servicePath;
            connection.context = context;
            connection.onSuccess = onSuccess;
            connection.onFailure = onFailure;
            connection.method = methodName;
            connection.serviceFormat = serviceFormat;

            try {
                this.onBeforeSend(methodName, args);
                for(var i in this.handlers['post'][servicePath]) {
                    var handler = this.handlers['post'][servicePath][i];
                    args = handler(args);
                }

                // Specify the function that will handle the HTTP response
                connection.request.onreadystatechange = function() { self.callbackOnReady(connection); };

                if (servicePath == undefined || servicePath.length == 0) {
                    new Error('Empty url for webservice');
                }
                connection.request.open('post', servicePath + '?rnd=' + (new Date().getTime()), true);

                // Set the Content-Type header for a POST request
                connection.request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                connection.request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                var postData = 'method=' + methodName;

                if (args != null) {
                    postData += '&argCount=' + args.length;

                    for (var i = 0; i < args.length; i++) {
                        if (args[i] && typeof args[i] == 'object') {
                            args[i] = self.toJson(args[i]);
                        }
                        postData += '&arg' + i + '=' + encodeURIComponent(args[i]);
                    }
                }

                connection.request.send(postData);
            } catch (errv) {
                onFailure("The application cannot contact the server at the moment. "
                        + "Please try again in a few seconds.\n" +
                          "Error detail: " + errv.message, context, methodName);
            }
        }
    },

    /**
     * quoteString(string)
     * Returns a string-repr of a string, escaping quotes intelligently.  
     * Mostly a support function for toJSON.
     *
     *  Examples:
     *      >>> quoteString("apple")
     *      "apple"
     *  
     *      >>> quoteString('"Where are we going?", she asked.')
     *      "\"Where are we going?\", she asked."
     *
     *  @source http://code.google.com/p/jquery-json/source/browse/trunk/jquery.json.js
     **/
    quoteString: function(string) {
        if (string.match(this.escapeable)) {
            return '"' + string.replace(this.escapeable, function(a) {
                var c = this.meta[a];
                if (typeof c === 'string') {
                    return c;
                }
                c = a.charCodeAt();
                return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
            }) + '"';
        }
        return '"' + string + '"';
    },

    /**
     * toJson(json-serializble)
     * Converts the given argument into a JSON respresentation.
     *
     *  If an object has a "toJSON" function, that will be used to get the representation.
     *  Non-integer/string keys are skipped in the object, as are keys that point to a function.
     *
     *  @source http://code.google.com/p/jquery-json/source/browse/trunk/jquery.json.js
     */
    toJson: function(o) {

        if (typeof(JSON) == 'object' && JSON.stringify) {
            return JSON.stringify(o);
        }

        var type = typeof(o);
    
        if (o === null) {
            return "null";
        }
    
        if (type == "undefined") {
            return undefined;
        }
        
        if (type == "number" || type == "boolean") {
            return o + "";
        }
    
        if (type == "string") {
            return this.quoteString(o);
        }
    
        if (type == 'object') {
            if (typeof o.toJSON == "function") {
                return this.toJSON(o.toJSON());
            }

            if (o.constructor === Date) {
                var month = o.getUTCMonth() + 1;
                if (month < 10) {
                    month = '0' + month;
                }

                var day = o.getUTCDate();
                if (day < 10) {
                    day = '0' + day;
                }

                var year = o.getUTCFullYear();
                
                var hours = o.getUTCHours();
                if (hours < 10) {
                    hours = '0' + hours;
                }
                
                var minutes = o.getUTCMinutes();
                if (minutes < 10) {
                    minutes = '0' + minutes;
                }
                
                var seconds = o.getUTCSeconds();
                if (seconds < 10) {
                    seconds = '0' + seconds;
                }
                
                var milli = o.getUTCMilliseconds();
                if (milli < 100) {
                    milli = '0' + milli;
                }
                if (milli < 10) {
                    milli = '0' + milli;
                }

                return '"' + year + '-' + month + '-' + day + 'T' +
                             hours + ':' + minutes + ':' + seconds + 
                             '.' + milli + 'Z"'; 
            }

            if (o.constructor === Array) {
                var ret = [];
                for (var i = 0; i < o.length; i++) {
                    ret.push(this.toJSON(o[i]) || "null");
                }

                return "[" + ret.join(",") + "]";
            }
        
            var pairs = [];
            for (var k in o) {
                var name;
                var type = typeof k;

                if (type == "number") {
                    name = '"' + k + '"';
                } else if (type == "string") {
                    name = this.quoteString(k);
                } else {
                    continue;  //skip non-string or number keys
                }
                if (typeof o[k] == "function") {
                    continue;  //skip pairs where the value is a function.
                }

                var val = this.toJSON(o[k]);

                pairs.push(name + ":" + val);
            }

            return "{" + pairs.join(", ") + "}";
        }
    },

    /**
     * Complete request
     */
    callbackOnReady: function(connection) {
        if (connection.request.readyState == 4) {
            switch (connection.request.status) {
                case 200:
                    var res = this.parseJson(connection);
                    for(var i in this.handlers['post'][connection.servicePath]) {
                        var handler = this.handlers['post'][connection.servicePath][i];
                        res = handler(res);
                    }
                    this.onAfterRecive(res, connection.context, connection.method);

                    connection.onSuccess(res, connection.context, connection.method);
                    break;
                case 403:
                case 500:
                    var result = this.parseJson(connection);
                    if (typeof console != 'undefined' && typeof console.error == 'function') {
                        var info =  "Method: " + connection.method + "\n";
                        console.error("Internal Server Error\n\n", info, "Response: ", result);
                    }
                    connection.onFailure(result, connection.context, connection.method);
                    return;
                default:
                    connection.onFailure('Invalid status encountered (' + connection.request.status + ')', connection.context, connection.method);
                    break;
            }
        }
    },

    /**
     * Parse json response from webservice
     */
    parseJson: function(connection) {
        var resp = connection.request.responseText;
        try {
            return eval('(' + resp + ')');
        } catch(ex) {
            if (!connection) {
                alert('Connection is null?');
            }
            connection.onFailure('An error has occurred: ' + ex.message + '; Response: ' + resp, connection.context, connection.method);
            return null;
        } finally {
            connection.busy = false;
        }
    }
}