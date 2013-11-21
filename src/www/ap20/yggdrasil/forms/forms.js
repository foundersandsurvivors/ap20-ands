var xmlHttp = createXmlHttpRequestObject();

function createXmlHttpRequestObject() {
    var xmlHttp;
    if(window.ActiveXObject) {
        try {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (e) {
            xmlHttp = false;
        }
    }
    else {
        try {
            xmlHttp = new XMLHttpRequest();
        }
        catch (e) {
            xmlHttp = false;
        }
    }
    if (!xmlHttp)
        alert("Error creating the XMLHttpRequest object.");
    else
        return xmlHttp;
}


function get_xml_source(id) {
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
        xmlHttp.open("GET", "/prot/xmldbAjax/convict/src/YMAP" + id, true);
        xmlHttp.onreadystatechange = display_xml_source;
        xmlHttp.send(null);
    }
    else
        setTimeout('process()', 1000);
}


function display_xml_source()  {
    if (xmlHttp.readyState == 4) {
        if (xmlHttp.status == 200) {
            response = xmlHttp.responseText;
            document.getElementById("xml_source").innerHTML = response;
            setTimeout('process()', 1000);
        }
        else {
          alert("There was a problem accessing the server: " + xmlHttp.statusText);
        }
    }
}

function get_source(id) {
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
        xmlHttp.open("GET", "get_source_string.php?id=" + id, true);
        xmlHttp.onreadystatechange = display_source;
        xmlHttp.send(null);
    }
    else
        setTimeout('process()', 1000);
}

function display_source() {
    if (xmlHttp.readyState == 4) {
        if (xmlHttp.status == 200) {
            response = xmlHttp.responseText;
            document.getElementById("source").innerHTML = response;
            setTimeout('process()', 1000);
        }
        else {
          alert("There was a problem accessing the server: " + xmlHttp.statusText);
        }
    }
}

function get_name(id) {
    if (xmlHttp.readyState == 4 || xmlHttp.readyState == 0) {
        xmlHttp.open("GET", "get_name_string.php?id=" + id, true);
        xmlHttp.onreadystatechange = set_name;
        xmlHttp.send(null);
    }
    else
        setTimeout('process()', 1000);
}

function set_name() {
    if (xmlHttp.readyState == 4) {
        if (xmlHttp.status == 200) {
            response = xmlHttp.responseText;
            document.getElementById("name").innerHTML = response;
            setTimeout('process()', 1000);
        }
        else {
          alert("There was a problem accessing the server: " + xmlHttp.statusText);
        }
    }
}
