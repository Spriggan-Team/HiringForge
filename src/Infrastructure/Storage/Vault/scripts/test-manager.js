


/* ==========================
   HTML SVG
   ========================== */


const drawerSvg  = 
`<?xml version="1.0" encoding="utf-8"?><!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
<svg fill="#000000" width="800px" height="800px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <path d="M0.256 8.606c0-0.269 0.106-0.544 0.313-0.75 0.412-0.412 1.087-0.412 1.5 0l14.119 14.119 13.913-13.912c0.413-0.412 1.087-0.412 1.5 0s0.413 1.088 0 1.5l-14.663 14.669c-0.413 0.413-1.088 0.413-1.5 0l-14.869-14.869c-0.213-0.213-0.313-0.481-0.313-0.756z"></path>
</svg>`


const reloadSvg  = `<?xml version="1.0" encoding="utf-8"?><!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools --><svg fill="#000000" width="800px" height="800px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M15.977 0c-7.994 0-14.498 6.504-14.498 14.498 0 7.514 5.79 13.798 13.236 14.44l-2.87 1.455c-0.354 0.195-0.566 0.632-0.355 0.977l0.101 0.262c0.211 0.346 0.668 0.468 1.021 0.274l4.791-2.453c0.006-0.004 0.012-0.003 0.019-0.007l0.322-0.176c0.177-0.098 0.295-0.257 0.342-0.434 0.049-0.177 0.027-0.375-0.079-0.547l-0.191-0.313c-0.003-0.006-0.009-0.010-0.012-0.015l-2.959-4.624c-0.21-0.346-0.666-0.468-1.021-0.274l-0.232 0.162c-0.354 0.194-0.378 0.694-0.168 1.038l1.746 2.709c-0.009-0-0.018-0.004-0.027-0.005-6.54-0.429-11.662-5.907-11.662-12.47 0-6.891 5.607-12.498 12.498-12.498 6.892 0 12.53 5.606 12.53 12.498 0 3.968-1.823 7.613-5 9.999-0.442 0.332-0.53 0.959-0.199 1.401 0.332 0.442 0.959 0.531 1.401 0.199 3.686-2.768 5.799-6.996 5.799-11.598-0-7.994-6.536-14.498-14.53-14.498z"></path></svg>` 


/* ==========================
   GLOBAL STATE
   ========================== */

//Stores the global authorization key



const inputUrl = document.getElementById("base-url");
const inputKey = document.getElementById("authorization-Key");



let AUTH_KEY = "";
let BASE_URL = "http://localhost:3000";


inputUrl.addEventListener("input", e => {
    BASE_URL = e.target.value;
})

inputKey.addEventListener("input", e => {
    AUTH_KEY = e.target.value;
});




/* ==========================
   DATA MODELS
   ========================== */


// Represents a query parameter definition



//Use to draw a json-like objetc in html format




class ValueType {
    static String = Symbol("String")
    static Number = Symbol("Number")
    static Array  = Symbol("Array")
    static Object = Symbol("Object")

    static extractValueType(variable){
        return typeof variable == "string" ? 
                    ValueType.String
                     : typeof variable == "number" ?
                        ValueType.Number
                            : Array.isArray(variable) ? 
                                ValueType.Array
                                    : typeof variable == "object" && ValueType.Object;
    }
}





class ObjektraField {
    constructor({key, type, value = null, ext = []}) {
        const validTypes = Object.values(ValueType)

        if (!validTypes.includes(type)) {
            throw new TypeError("[ObjektraField] Invalid ValueType")
        }

        this.key = key
        this.type = type
        if(type == ValueType.Object){
            this.ext = ext  //array of ObjektraField (used when type is equal to object)
            return;
        }
        this.value = value
    }


    parseIntoObject(){
        if(this.type != ValueType.Object){
            return { [this.key]: this.value }
        }
        else if(Array.isArray(this.ext)){
            let source;
            for (let i = 0; i < this.ext.length; i++) {
                const target = this.ext[i].parseIntoObject();
                Object.assign(source, target)
            }
            return source;
        }
    }

}






class Objektra {
    constructor(entries, HShift = 20, className = "json-object-text", mode = "simple-json-object", flexibility = 0){
        if(!Array.isArray(entries)){
            console.log("[Objektra] Bad entries");
            return;
        }

        this.entries = entries;      // array of ObjektraField
        this.className = className;
        this.HShift = HShift;
        this.mode = mode;

        this.canvas = document.createElement('div');
        if(mode == "simple-json-object"){
            this.buildJSONObjectView(this.canvas, this.entries, HShift);
        }
        else{}
    }

    getHTMLNode(){
        return this.canvas;
    }

    buildJSONObjectView(rootElement, entries, HShift, Depth = 1){

        if(Array.isArray(entries) && entries.length == 0){
            return
        }
        // Left Bracket
        const lBracket = document.createElement('div');
        lBracket.textContent = '{';
        lBracket.className = 'bracket';
        rootElement.appendChild(lBracket);

        // Centent
        entries.forEach(entry => {
            if(!(entry instanceof ObjektraField))
                throw TypeError("[Objektra] Invalid Entries");

            const inputableKeySection = document.createElement('div');
            inputableKeySection.className = "inputable-key-section";
            inputableKeySection.style.marginLeft = `${Depth * HShift}px`;

            // "+" to signal an object
            const mark = document.createElement('span');
            mark.className = this.className;
            mark.textContent = "+";

            // Key
            const keyNode = document.createElement('span');
            keyNode.className = this.className;
            keyNode.textContent = `${entry.key}: `;

            inputableKeySection.append(mark, keyNode);

            //Inpt for embedded object
            if(entry.type === ValueType.Object){
                const subObjectRootElement = document.createElement('div');
                subObjectRootElement.className = 'json-object-container';
                this.buildJSONObjectView(subObjectRootElement, entry.ext, HShift, Depth + 1);
                inputableKeySection.appendChild(subObjectRootElement);
            }
            else {
                // Input fro simple type
                const input = document.createElement('input');
                input.style.cursor = 'text';
                input.placeholder = entry.type.toString().replace('Symbol(','').replace(')','');

                input.addEventListener("keydown", (event)=>{
                    if(event.key === "Enter"){}
                })
                input.addEventListener('input', (event)=>{
                    entry.value = event.target.value
                })
                inputableKeySection.appendChild(input);
            }

            rootElement.appendChild(inputableKeySection);
        });

        // Right Bracket
        const rBracket = document.createElement('div');
        rBracket.textContent = '}';
        rBracket.className = 'bracket';
        rootElement.appendChild(rBracket);
    }

    //Transforme entries object into real Javascript basics object ({key: value, ....})
    static parseObjektraEntriesIntoObject(entries){
        if(Array.isArray(entries)){
            let obj = {};
            for (let index = 0; index < entries.length; index++) {
                const target = entries[index].parseIntoObject();
                Object.assign(obj, target)
            }
            return obj
        }
        return null;
    }

    static parseObjectIntoObjektraFields(){

    }
}






class Query {
    constructor({key, mandatory = false}) {
        this.key = key;
        this.value = null;
        this.mandatory = mandatory;
    }


    getInputHTMLNode(){
        const input = document.createElement('input');
        input.placeholder = this.key;
        input.addEventListener('input', (even) => this.value = even.target.value )
        return input;
    }
}






class Parameter {
    constructor({ key, inputEventCallback }) {
        this.key = key;
        this.value = null;
        this.inputEventCallback = inputEventCallback;
    }

    getInputHTMLNode(){
        const input = document.createElement('input');
        input.placeholder = this.key;
        input.addEventListener('input', (even) => {
            this.value = even.target.value
            if(this.inputEventCallback)
                this.inputEventCallback(this.value)
        })
        return input;
    }
}




// Represents an API endpoint definition





class Endpoint {
    constructor({ path, inputObjektraEntries = null, outputObjektraEntries = null, queries = [] })
    {
        this.pathTemplate = path;   // "/posts/{accountId}"
        this.path = path;           // path display / used
        this.pathHTMLNode = null

        this.inputObjektraEntries = inputObjektraEntries;
        this.outputObjektraEntries = outputObjektraEntries;

        this.queries = queries;
        this.params = this.extractParams();
    }

    extractParams() {
        const regex = /\{([^}]+)\}/g;
        const matches = [...this.pathTemplate.matchAll(regex)];

        return matches.map(match => {
            const paramName = match[1];

            return new Parameter({
                key: paramName,
                inputEventCallback: (value) => {
                    this.updatePath();
                }
            });
        });
    }

    updatePath() {
        let newPath = this.pathTemplate;

        this.params.forEach(p => {
            const replacement = p.value
                ? p.value
                : `{${p.key}}`;

            newPath = newPath.replace(`{${p.key}}`, replacement);
        });

        this.path = newPath;
        if(this.pathHTMLNode)
            this.pathHTMLNode.textContent = newPath
    }

    getPathHTMLNode(){
        if(this.pathHTMLNode){
            return this.pathHTMLNode
        }

        this.pathHTMLNode = document.createElement("div");
        this.pathHTMLNode.className = "endpoint";
        this.pathHTMLNode.textContent = this.path;
        
        return this.pathHTMLNode
    }


    getQueriesStringinfied() {
        if (!Array.isArray(this.queries) || this.queries.length === 0) {
            return "";
        }

        const params = this.queries
            .filter(q => q.value !== null && q.value !== "")
            .map(q => `${encodeURIComponent(q.key)}=${encodeURIComponent(q.value)}`);

        return params.length ? `?${params.join("&")}` : "";
    }

}




// Represent a badge

class RouteBadge{
    constructor(text){
        this.text = text
        this.rootContainer = document.createElement('div')
        this.rootContainer.className = "route-badge"
        this.rootContainer.textContent = this.text
    }

    getHTMLNode(){
        return this.rootContainer
    }
}




// Represents an HTTP route (method + endpoint)




class Route {
    constructor({endpoint, method = "GET", description = "", badges = null, headers=  { "Content-Type": "application/json" }}) {
        this.method = method;
        this.endpoint = endpoint;
        this.description = description
        this.response = null;
        this.headers = headers
        this.badges = badges

        this.counter = 0;

        this.reloader = document.createElement("div")
        this.reloader.classList.add('visibility-hidden', 'svg-box')
        this.reloader.innerHTML = reloadSvg;

        this.drawer = document.createElement("div")
        this.drawer.className = "svg-box"
        this.drawer.innerHTML = drawerSvg

        this.content = null;
        this.responseContainer = null
        this.rootContainer = document.createElement("div");
    }


    paint(){
        //---base 

        const methodEl = document.createElement("div");
        methodEl.className = `method ${this.method}`;
        methodEl.textContent = this.method;

        const endpointEl = this.endpoint.getPathHTMLNode()

        //----leading

        const leadingContent = document.createElement('div')
        leadingContent.className = "route-leading"

        if(Array.isArray(this.badges) && this.badges.length > 0){
            this.badges.forEach((badge)=> leadingContent.appendChild(badge.getHTMLNode()))
        }
        leadingContent.append(this.reloader, this.drawer)


        //-----Entry

        const routeEntry = document.createElement('div')
        routeEntry.className = "route";
        routeEntry.append(methodEl, endpointEl, leadingContent)
        
        this.rootContainer.appendChild(routeEntry);
        // console.log("Route", this.endpoint.inputObjektraEntries ,this.endpoint.outputObjektraEntries, this.endpoint.queries.length, this.endpoint.params)

        if(
            this.endpoint.inputObjektraEntries 
            || this.endpoint.outputObjektraEntries
            || this.endpoint.queries.length  > 0
            || this.endpoint.params.length > 0
        ){
            const rootHandler = this.drawRootContentHandler()
            routeEntry.addEventListener('click', ()=>{
                if(this.content){
                    this.toggleContentVisibility()
                }
            })
            rootHandler.appendChild(this.getResponseSectionHTMLNode())
            this.rootContainer.appendChild(rootHandler);
        }
        else{
            this.content = this.getResponseSectionHTMLNode()
            this.rootContainer.appendChild(this.content);
        }
    }

    //---Handlers
    drawRootContentHandler(){
        this.content = document.createElement('div');
        this.content.classList.add( "display-none", "visibility-hidden");

        // Parameters
        if (this.endpoint.params.length > 0) {
            this.content.appendChild(this.getParamatersSectionHTMLNode())
        }

        // Queries
        if (this.endpoint.queries.length > 0) {
            this.content.appendChild(this.getQuerySectionHTMLNode())
        }

        // Input object
        if(this.endpoint.inputObjektraEntries){
            const inputObjektra = new Objektra(this.endpoint.inputObjektraEntries);
            const inputSection = document.createElement('div');
            inputSection.className = 'input-schema-container';
            inputSection.append(this.getHeaderContentTypeHTMLNode(), inputObjektra.getHTMLNode());
            this.content.appendChild(inputSection);
        }

        // Output object
        if(this.endpoint.outputObjektraEntries){
            const outputObjektra = new Objektra(this.endpoint.outputObjektraEntries);
            const outputSection = document.createElement('div');
            outputSection.className = 'output-schema-container';
            outputSection.appendChild(outputObjektra.getHTMLNode());
            this.content.appendChild(outputSection);
        }

        this.content.appendChild(this.getCallerBtnSectionHTMLNode());
        return this.content;
    }



    toggleContentVisibility(){
        this.content.classList.toggle('display-none')
        this.content.classList.toggle('route-content')
        this.content.classList.toggle('visibility-hidden')
    }


    //HTML helpers

    //HTMLResponseNode    (the section in the content used to draw the response)
    getResponseSectionHTMLNode(){
        const wrapper = document.createElement('div')
        wrapper.className = "route-response-container"
        this.responseContainer = wrapper
        return this.responseContainer
    }
    
    getHeaderContentTypeHTMLNode(){
        // Content-Type option
        const httpContentTypeOption  = document.createElement('select')
        httpContentTypeOption.className = "header-options"
        if(this.headers['Content-Type'] == "application/json"){
            const httpJsonContentOption  = document.createElement('option')
            httpJsonContentOption.selected = true
            httpJsonContentOption.textContent = "JSON"
            httpContentTypeOption.append(httpJsonContentOption)
        }
        else{}
        return httpContentTypeOption
    }


    getParamatersSectionHTMLNode(){
        const paramsSection = document.createElement('div')
        paramsSection.className = 'params-section'

        const title = document.createElement('div')
        title.className = 'title'
        title.textContent = "Parameters"

        const inputsRow = document.createElement('div')
        inputsRow.className = 'inputs-row'

        this.endpoint.params.forEach(param => {
            inputsRow.appendChild(param.getInputHTMLNode())
        })

        paramsSection.append(title, inputsRow)
        return paramsSection
    }
    

    getQuerySectionHTMLNode(){
        const querySection = document.createElement('div')
        querySection.className = 'query-section'

        const title = document.createElement('div')
        title.className = 'title'
        title.textContent = "Queries"

        const inputsRow = document.createElement('div')
        inputsRow.className = 'inputs-row'

        this.endpoint.queries.forEach(query => {
            inputsRow.appendChild(query.getInputHTMLNode())
        })

        querySection.append(title, inputsRow)
        return querySection;
    }

    getCallerBtnSectionHTMLNode(){
        const btnSection = document.createElement('div')
        btnSection.className = "fetcher-btn-section"

        const btn = document.createElement('button');
        btn.className = "route-btn-executer";
        btn.textContent = "Execute";
        btnSection.appendChild(btn)

        const spinner = document.createElement('div')
        spinner.className = "execution-spinner"
        btnSection.appendChild(spinner)

        btn.addEventListener('click', ()=> this.execute());
        return btnSection
    }


    // Fetch Call

    async execute(){
        const body = Objektra.parseObjektraEntriesIntoObject(this.endpoint.inputObjektraEntries);
        console.log(BASE_URL + this.endpoint.path + this.endpoint.getQueriesStringinfied())
        console.log(body)

        this.response = await fetch(
            BASE_URL + this.endpoint.path + this.endpoint.getQueriesStringinfied(),
            this.buildFetchOptions({body, headers: this.headers})
        )

        const data = await response.json()
        this.responseContainer.className = "response-section"
        this.incrementCounter()
    }

    // Builds a fetch configuration using the global auth key
     
    buildFetchOptions({ body = null, headers }) {
        return {
            method: this.method,
            headers: {...headers, "Authorization": AUTH_KEY },
            body: body ? JSON.stringify(body) : null
        };
    }

    incrementCounter(){
        this.counter++;
        if(this.counter != 0){
            this.reloader.classList.remove('hidden')
        }
    }


}




 //Represents a Resource (group of routes)

class Resource {
    constructor(name, routes) {
        this.name = name;
        this.routes = routes;
    }


    render(container) {
        const resourceEl = document.createElement("div");
        resourceEl.className = "resource";

        const title = document.createElement("h2");
        title.className = "resource-title";
        title.textContent = this.name;

        resourceEl.appendChild(title);

        this.routes.forEach(route => {
            resourceEl.appendChild(this.renderRoute(route));
        });

        container.appendChild(resourceEl);
    }

     //* Render a single route block

    renderRoute(route) {
        route.paint()
        return route.rootContainer;
    }
}




/* ==========================
   APP INITIALIZATION
   ========================== */


const container = document.getElementById("container");



// Declare API resources
 


//POST

const postResource = new Resource("Post", [
    new Route({
        endpoint: new Endpoint({ 
            path: "/posts/{accountId}/{uuid}",
        }),
        method: "GET",
        description: "This one is used to get all the post from the Api",
        badges: [new RouteBadge("PUBLIC"), new RouteBadge("Collection")]
    }),
    new Route({
        endpoint: new Endpoint({
            path: "/posts/{accountId}",
            queries: [new Query({key: "uuid", mandatory: true})],
        }),
        method: "GET",
        description: "This one is used to get one specific post from the Api"
    }),
    new Route({
        endpoint: new Endpoint({
            path: "/posts",
            inputObjektraEntries: [
                new ObjektraField({ key: 'title', type: ValueType.String }),
                new ObjektraField({ key: 'content', type: ValueType.Array }),
            ],
        }),
        method: "POST"
    }),
    new Route({
        endpoint: new Endpoint({
            path: "/posts",
            inputObjektraEntries: [
                new ObjektraField({ key: "uuid", type: ValueType.String   }),
                new ObjektraField({ key: "accountId", type: ValueType.String }),
                new ObjektraField({ key: "title", type: ValueType.String  }),
                new ObjektraField({ key: "content", type: ValueType.Array }),
            ]
        }),
        method: "PATCH"
    }),
    new Route({
        endpoint: new Endpoint({
            path: "/posts/{accountId}",
            queries: [new Query({key: "uuid", mandatory: true})]
        }),
        method: "DELETE"
    }),
]);



//Account

const accountRessource = new Resource("Account", [
    new Route({
        endpoint: new Endpoint({ 
            path: "/account/{accountId}",
        }),
        method: "GET"
    }),
    new Route({
        endpoint: new Endpoint({ path: "/account" }),
        method: "DELETE"
    }),

]);





// Render all resources


postResource.render(container);
accountRessource.render(container);

