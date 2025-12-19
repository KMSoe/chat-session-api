$(document).on('click', '.lfm' , function(e) {
    var type = $(this).data('filetype') ?? 'image';
    var route_prefix = '/filemanager';
    var target_input = $('#' + $(this).data('input'));
    var lfmImageInput = $(this).data('input');
    var lfmPtype  = $(this).data('ptype')
    var target_preview = $('#' + $(this).data('preview'));
    var deviceimg = $(this).data('device');
    var mh = deviceimg == "m" ? 16 : 16;

    var imageHeight = 'height:'+ $(this).data('height') ?? '16rem';
     

    window.open(route_prefix + '?type=' + type, 'FileManager', 'width=900,height=600');
    window.SetUrl = function (items) {
      var allfiles = "";
      var file_path = items.map(function (item) {
        return item.url;
      }).join(',');

      const ofile = target_input.val();
      if(ofile && type == "gallery"){
         allfiles = ofile+","+file_path
      }else{
        allfiles = file_path;
      }

      console.log(allfiles,"all files");
      // set the value of the desired input to image url

      target_input.value = allfiles;

      target_input.val('').attr('value', allfiles).val(allfiles).trigger('input');
      console.log(lfmImageInput);
      document.getElementById(lfmImageInput).dispatchEvent(new Event('input'));

      // clear previous preview
      target_preview.html('');

      // set or change the preview image src
      if (type !== 'file') {
        allfiles.split(',').forEach(function (item,key) {
          let imgElement ="";
          if(lfmPtype != "g"){
            imgElement = "<div class='position-relative w-100 "+lfmImageInput+"lfmc"+key+"'><img src='"+item+"' class='w-100' style='"+imageHeight+" !important;'><button type='button' onclick='removeImage(\""+lfmImageInput+"\","+key+")' class='btn btn-danger position-absolute top-0 end-0'>X</button></div>";
          }else{
            console.log("ag");
            imgElement += "<li class='lfmimage-container draggable w-100 draggableItem"+key+" "+lfmImageInput+"lfmc"+key+"' draggable='true'>";
            imgElement += "<input type='hidden' name='image_order[]' value='"+item+"' id='galleryImage"+key+"'>";
            imgElement += "<img src='"+item+"' class='lfmimage w-100'><div><button type='button' onclick='removeGImage(this)' class='btn btn-sm btn-danger w-100'>Remove</button></li>"
          }
          target_preview.append(imgElement);
        });
        target_preview.trigger('change');
      }
    };
    return false;
});

function removeImage(tinput,index){
    let inputimgs = $('#'+tinput).val().split(',');
    console.log("Removed ",inputimgs);
    inputimgs.splice(0,1);
    $('.'+tinput+'lfmc'+index).remove();
    $('#'+tinput).val(inputimgs.join(','));
    document.getElementById(tinput).dispatchEvent(new Event('input'));
}