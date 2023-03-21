/** Creates a basic slider
 * @param {string} name (optional) the unique name of the checkbox
 * @return {Element} the checkbox element of the slider
 * */
function createSlider(name=""){

    // label holds everything together
    label = document.createElement("label");
    label.setAttribute("class", "switch");

    // checkbox is an invisible checkbox that provides the functionality of the slider
    checkbox = document.createElement("input");
    checkbox.setAttribute("type", "checkbox");
    if (name) {
        checkbox.setAttribute("name", name);
    }
    

    // toggle displays the status of the slider
    toggle = document.createElement("span");
    toggle.setAttribute("class", "toggle");

    label.appendChild(checkbox);
    label.appendChild(toggle);

    return checkbox;
}

class EventCategoryManager {

    /** 
     * @param {Object} json a parsed json file giving the layout of event categories
     * @param {Element} mainPanel a container for the display of event categories
     */
    constructor(json, mainPanel) {
        
        this.mainPanel = mainPanel;
        this.eventCategories = {}; // event categories can be quickly accessed here
        this.eventCategoriesList = []; // event categories are ordered here

        for (var key in json) {
            name = this.createName(key);
            var evtCat = this.addEventCategory(name, key);
            json[key]["child_categories"].forEach((val, ind, ary) => {
                evtCat.addSubcategory(this.createName(val));
            });
        }
    }

    /** Creates a name from the json input key (used in constructor)
     * @param {string} key the key used to describe an event category in json
     * @return {string} the name the event category will be displayed with
     */
    createName(key){
        key = key.replace("_", " ");
        key = key.replace(/(^\w|\s\w)/g, m => m.toUpperCase());
        return key;
    }

    /** Creates and adds an event category to the end of the displayed list
     * @param {string} name the name of the event category
     * @return {EventCategory} the event category that was created
     * */
    addEventCategory(name, raw_name){
        this.eventCategories[name] = new EventCategory(name, this.eventCategories, raw_name);
        this.eventCategoriesList.push(name);
        return this.eventCategories[name];
    }

    /** Displays all of the event categories  */
    display(){
        var ul = document.getElementById("side-panel").firstElementChild;
        this.eventCategoriesList.forEach((val, ind, ary) => {
            ul.appendChild(this.eventCategories[val].createElements(true));
        });
    }

}

class EventCategory {

    /**
     * @param {string} name the name of the event category
     * @param {dictionary} eventCategories a reference to a dictionary containing all of the 
     * event categories
     * @param {string} raw_name the key value that came with this
     */
    constructor(name, eventCategories, raw_name){
        this.name = name;
        this.raw_name = raw_name
        this.subcategories = [];
        this.eventCategories = eventCategories;
        this.included = false;
        this.excluded = false;
        this.toggles = [];
    }
    addSubcategory(subcategory) {
        this.subcategories.push(subcategory);
    }
    isToggled(){
        return this.included;
    }
    toggleOn() {
        this.included = true;
        this.excluded = false;
        this.toggles.forEach((val, ind, ary) => {
            val.checked = true;
        });
        this.subcategories.forEach((val, ind, ary) => {
            this.eventCategories[val].toggleOn();
        });
    }
    toggleOff() {
        this.included = false;
        this.excluded = false;
        this.toggles.forEach((val, ind, ary) => {
            val.checked = false;
        });
        this.subcategories.forEach((val, ind, ary) => {
            this.eventCategories[val].toggleOff();
        });
    }
    parentToggleOn(){
        this.toggleOn();
    }
    parentToggleOff(){
        this.toggleOff();
    }
    toggle(){
        if(this.included === false){
            this.toggleOn();
        } else {
            this.toggleOff();
        }
        
    }

    /** Creates the ui elements for the event category and returns the created elements
     * @param {boolean} toplevel toplevel is true if these elements will not be nested 
     * within other event category containers
     * @return {Element} the container of created elements
     */
    createElements (toplevel){
        
        // li holds everything together and handles expansion of subcategories
        var li = document.createElement("li");
        li.setAttribute("class", "event-category")

        // slider allows the event category to be toggled
        var slider = null;
        if (toplevel) slider = createSlider(this.raw_name);
        else slider = createSlider();
        this.toggles.push(slider);
        slider.addEventListener("click", ()=>{this.toggle()});
        
        // name lets the user know what the name of this event category is
        var name = document.createElement("span");
        name.appendChild(document.createTextNode(this.name));

        li.appendChild(slider.parentNode);
        li.appendChild(name);

        // handles the display of subcategories
        if (this.subcategories.length > 0) {
            var ul = document.createElement("ul");
            li.classList.toggle("hidden");
            li.appendChild(ul);
            li.onmouseenter = function(evt){
                evt.srcElement.classList.toggle("hidden");
            }
            li.onmouseleave = function(evt){
                evt.srcElement.classList.toggle("hidden");
            }
            
            this.subcategories.forEach((val, ind, array) => {
                if (!(val in this.eventCategories)) {
                    console.error(`${val} is not in eventCategories!`);
                    return;
                }
                ul.appendChild(this.eventCategories[val].createElements(false));
            });
        }

        return li;
    }
    

}

fetch("/event_categories_public.json", {method: "GET", }).then((response) => {
    if (response.ok){
        response.json().then((json) => {
            var eventCategoryManager = new EventCategoryManager(json);
            eventCategoryManager.display();
        })
    } else {
        console.error(`Request to /event_categories_public.json failed with ${response.status}`);
    }
});