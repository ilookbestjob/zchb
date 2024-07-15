let datapath="\\zchb_backend\\data.json"
let data

$(async function(){

data = await fetch(datapath)
data = await data.json()

buildData("body", data)

})

function buildData(target,data){

$(target).html("")


data.body.success.forEach(element => {

    $(target).append(`<div class="row"><div>`+element.name+`</div><div>`+element.desc+`</div><div>OK</div></div>`)
    
});



}