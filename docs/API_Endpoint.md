# API Endpoint

Many of the API endpoint provided from Wordpress are in used for the corporate website and you can see a list of them in this file 
https://github.com/Crown-Commercial-Service/ccs-frontend/blob/development/config/content/content-model.yaml

<br />

## Environment 

**DEV -> PREPROD(UAT) -> PROD**

### Backend 
* DEV = https://webdev-cms.crowncommercial.gov.uk/
* UAT = https://webuat-cms.crowncommercial.gov.uk/
* PROD = https://webprod-cms.crowncommercial.gov.uk/

In the following section, those value will be shorthanded as `[cms-url]`

### Frontend 
* DEV = https://webdev.crowncommercial.gov.uk/
* UAT = https://webuat.crowncommercial.gov.uk/
* PROD = https://webprod.crowncommercial.gov.uk/ or https://www.crowncommercial.gov.uk/

In the following section, those value will be shorthanded as `[front-url]`

<br />

## Mapping between Wordpress and Frontend

### **Posts == News**
On Wordpress CMS, you can see posts on the left side of the panel and those are represent as news on the frontend. See here https://www.crowncommercial.gov.uk/news

* To access a list of posts: `[cms-url]wp-json/wp/v2/posts`
* To access a specific posts: `[cms-url]wp-json/wp/v2/posts/[post_id]`

`[post_id]` can be found by visiting Wordpress CMS and the number from the URL is the post_id **OR** use the list of posts API endpoint above.

<br />

### **Events == Events** 

* To access a list of events: `[cms-url]wp-json/wp/v2/event`
* To access a specific events: `[cms-url]wp-json/wp/v2/posts/[event_id]`

`[event_id]` can be found by visiting Wordpress CMS and the number from the URL is the event_id **OR** use the list of posts API endpoint above.

<br />

### **Supplier == Supplier** 

* To access a list of supplier: `[cms-url]wp-json/ccs/v1/suppliers`
* To access a specific supplier: `[cms-url]wp-json/ccs/v1/suppliers/[supplier_id]`

`[supplier_id]` can be found by visiting Wordpress CMS and the number from the URL is the supplier_id **OR** use the list of posts API endpoint above.

The search functionality on frontend uses elasticsearch which is another endpoint `[cms-url]search-api/suppliers`. 

<br />

### **Framework == Framework** 

* To access a list of framework: `[cms-url]wp-json/ccs/v1/frameworks`
* To access a specific framework: `[cms-url]wp-json/ccs/v1/frameworks/[framework_id]`

`[framework_id]` can be found by visiting Wordpress CMS and the number from the URL is the framework_id **OR** use the list of posts API endpoint above.

The search functionality on frontend uses elasticsearch which is another endpoint `[cms-url]search-api/frameworks`. You can also define the filter here with `?status[]=Live`

For example, endpoint will return the same result as if the user were using the search on frontend in DEV and have filter their result as expired framework only. https://webdev-cms.crowncommercial.gov.uk/search-api/frameworks?status[]=EXPIRED


* Lot: This is an object type belonging to framework and cannot be view from the frontend on it own.
For more infomation, you can vist here `[cms-url]wp-json/ccs/v1`

<br />

### **Page == Page(URL link are defined from Wordpress)**

* To access a list of page: `[cms-url]wp-json/wp/v2/pages`
* To access a specific page: `[cms-url]wp-json/wp/v2/pages/[page_id]`

`[page_id]` can be found by visiting Wordpress CMS and the number from the URL is the page_id **OR** use the list of posts API endpoint above.

The following objects belongs to the page and cannot be view from the frontend on it own.
* Whitepapers
    * To access a list of whitepaper: `[cms-url]wp-json/wp/v2/whitepaper`
    * To access a specific whitepaper: `[cms-url]wp-json/wp/v2/whitepaper/[whitepaper_id]`
* Webinars
    * To access a list of webinar: `[cms-url]wp-json/wp/v2/webinar`
    * To access a specific webinar: `[cms-url]wp-json/wp/v2/webinar/[webinar_id]`
* Digital Brochures
    * To access a list of digital brochure: `[cms-url]wp-json/wp/v2/digital_brochure`
    * To access a specific digital brochure: `[cms-url]wp-json/wp/v2/digital_brochure/[digital_brochure_id]`

    Downloadable Resources
    * To access a list of downloadable resources: `[cms-url]wp-json/wp/v2/downloadable`
    * To access a specific downloadable resource: `[cms-url]wp-json/wp/v2/downloadable/[downloadable_resources_id]`


## Others

Information on some of the pages on frontend are pulled from wordpress CMS with custome endpoint. 


CMS Name | Frontend page (URL)| API endpoint
--- | --- | ---
eSourcing Training Dates |  `[front-url]esourcing-training`   |   `[cms-url]wp-json/ccs/v1/esourcing-dates/0`
Page option cards<br /><sub><sup>This will appear on pages that is using the default template and have the "Include Option Cards" checked</sup></sub> |  `[front-url]buy-and-supply`       |   `[cms-url]wp-json/ccs/v1/option-cards/0`
Upcoming Deals Page      |  `[front-url]agreements/upcoming`  |   `[cms-url]wp-json/ccs/v1/upcoming-deals-page/0`
Homepage Components      |  `[front-url]`                     |   `[cms-url]wp-json/ccs/v1/homepage-components/0`
Redirection              |   N/A                              |   `[cms-url]wp-json/ccs/v1/redirections/0`