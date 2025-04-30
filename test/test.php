<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Soạn thảo bài viết</title>
</head>

<body>

  <form method="POST" action="save.php">
    <textarea id="editor" name="content"></textarea>
    <button type="submit">Lưu bài viết</button>
  </form>

  <script src="js/tinymce/tinymce.min.js"></script>

  <script>
    // tinymce.init({
    //   selector: "#editor",
    //   language: "vi",
    //   width: "100%",
    //   height: 500,
    //   menubar: false,
    //   statusbar: true,

    //   plugins: "advlist autolink lists link image charmap print preview anchor " +
    //     "searchreplace visualblocks code fullscreen " +
    //     "insertdatetime media table paste code help wordcount responsivefilemanager",

    //   toolbar: "undo redo | formatselect | bold italic underline forecolor backcolor | " +
    //     "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | " +
    //     "link image media table | code | responsivefilemanager",

    //   external_filemanager_path: "responsive_filemanager/filemanager/",
    //   filemanager_title: "Quản lý tập tin",
    //   external_plugins: {
    //     filemanager: "responsive_filemanager/filemanager/plugin.min.js"
    //   },

    //   file_picker_types: 'image',
    //   image_advtab: true,
    //   image_caption: true,

    //   images_upload_url: "upload.php",
    //   automatic_uploads: false,

    //   images_upload_handler: function(blobInfo, success, failure) {
    //     return new Promise(function(resolve, reject) {
    //       var xhr = new XMLHttpRequest();
    //       var formData = new FormData();

    //       xhr.open('POST', 'upload.php');

    //       xhr.onload = function() {
    //         if (xhr.status !== 200) {
    //           reject('HTTP Error: ' + xhr.status);
    //           return failure && failure('HTTP Error: ' + xhr.status);
    //         }

    //         let json;
    //         try {
    //           json = JSON.parse(xhr.responseText);
    //         } catch (e) {
    //           reject('Invalid JSON');
    //           return failure && failure('Invalid JSON');
    //         }

    //         if (!json || typeof json.location !== 'string') {
    //           reject('Invalid response JSON');
    //           return failure && failure('Invalid response JSON');
    //         }

    //         success(json.location);
    //         resolve();
    //       };

    //       xhr.onerror = function() {
    //         reject('XHR error');
    //         failure && failure('XHR Transport Error');
    //       };

    //       formData.append('file', blobInfo.blob(), blobInfo.filename());
    //       xhr.send(formData);
    //     });
    //   }
    // });



    tinymce.init({
      selector: "textarea",
      theme: "modern",
      width: 680,
      height: 300,
      plugins: [
        "advlist autolink link image lists charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons paste textcolor responsivefilemanager code"
      ],
      toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
      toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
      image_advtab: true,

      external_filemanager_path: "/test/filemanager/",
      filemanager_title: "Responsive Filemanager",
      external_plugins: {
        "filemanager": "/test/js/tinymce/plugins/responsivefilemanager/plugin.min.js"
      }
    });
  </script>

</body>

</html>