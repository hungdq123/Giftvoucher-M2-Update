var counter = 0;
function AddFileUpload() {
    var div = document.createElement('div');
    div.innerHTML = '<input id="image' + counter + '" name = "images[]" type="file" />' +
            '&nbsp;<button id="remove_image' + counter + '" title="Remove" type="button" class="scalable delete delete-select-row icon-btn" onclick="RemoveFileUpload(this)"><span>Remove</span></button>'
            ;
    document.getElementById("FileUploadContainer").appendChild(div);
    counter++;
    $('number_image').value=counter;
}
function RemoveFileUpload(div) {
    document.getElementById("FileUploadContainer").removeChild(div.parentNode);
    counter--;
    $('number_image').value=counter;
}
    