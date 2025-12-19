<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head><base href="">
		<title>CFA | Dashboard</title>
		<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
		<link rel="shortcut icon" href="{{ asset('frontend/img/favicon.png') }}" type="image/x-icon">
		<link rel="icon" href="{{ asset('frontend/img/favicon.png') }}" type="image/x-icon">
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Page Vendor Stylesheets(used by this page)-->
		<link href="{{ asset('backend/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('backend/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Page Vendor Stylesheets-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="{{ asset('backend/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('backend/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('backend/css/custom-style.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		<script src="{{asset('backend/js/vue3.js')}}"></script>
		<script src="{{asset('backend/js/jquery.js')}}"></script>
		<script src="{{asset('backend/js/custom/tinymce/tinymce.bundle.js')}}"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
		<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
		<!--begin::Main-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Page-->
			<div class="page d-flex flex-row flex-column-fluid">
				<!--begin::Aside-->
				 @include('admin.sidebar')
				<!--end::Aside-->
				<!--begin::Wrapper-->
				<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
					<!--begin::Header-->
					@include('admin.header')
					<!--end::Header-->
					<!--begin::Content-->
                    @yield('content')
					<!--end::Content-->
					<!--begin::Footer-->
					@include('admin.footer')
					<!--end::Footer-->
				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Page-->
		</div>
		<!--end::Root-->
		<!--end::Main-->
		<!--begin::Scrolltop-->
		<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
			<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
			<span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
				</svg>
			</span>
			<!--end::Svg Icon-->
		</div>
		<!--end::Scrolltop-->
		<!--begin::Javascript-->
		<script>var hostUrl = "assets/";</script>
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="{{ asset('backend/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('backend/js/scripts.bundle.js') }}"></script>
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Vendors Javascript(used by this page)-->
		{{-- <script src="{{ asset('backend/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script> --}}
		<script src="{{ asset('backend/plugins/custom/datatables/datatables.bundle.js') }}"></script>
		<!--end::Page Vendors Javascript-->
		<!--begin::Page Custom Javascript(used by this page)-->
		{{-- <script src="{{ asset('backend/js/widgets.bundle.js') }}"></script>
		<script src="{{ asset('backend/js/custom/widgets.js') }}"></script> --}}
		{{-- <script src="{{ asset('backend/js/custom/apps/chat/chat.js') }}"></script>
		<script src="{{ asset('backend/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
		<script src="{{ asset('backend/js/custom/utilities/modals/create-app.js') }}"></script>
		<script src="{{ asset('backend/js/custom/utilities/modals/users-search.js') }}"></script> --}}
		<!--end::Page Custom Javascript-->
		{{-- <script src="{{ asset('backend/js/custom/apps/customers/list/export.js') }}"></script>
		<script src="{{ asset('backend/js/custom/apps/customers/list/list.js') }}"></script>
		<script src="{{ asset('backend/js/custom/apps/customers/add.js') }}"></script> --}}
		{{-- <script src="{{ asset('backend/js/standlone.js') }}"></script> --}}
		<script src="{{ asset('backend/js/lfm.js') }}"></script>
		<script src="{{ asset('backend/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        var editor = {
            selector: ".editor",
            height: "250",
            menubar: false,
            forced_root_block : "",
            toolbar: ["styleselect fontselect fontsizeselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview | table | code | codesample",
            ],
            relative_urls: false,
            remove_script_host: true,

            images_upload_handler: function(blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', '/upload');
                var token = '{{ csrf_token() }}';
                xhr.setRequestHeader("X-CSRF-Token", token);
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);

                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    success('{{ url('') }}' + json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            },
            directionality: 'ltr',

             plugins: 'image code table lists link',

            /* enable title field in the Image dialog*/
            image_title: true,
            /* enable automatic uploads of images represented by blob or data URIs*/
            automatic_uploads: true,
            /*
            URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
            images_upload_url: 'postAcceptor.php',
            here we add custom filepicker only to Image dialog
            */
            file_picker_types: 'image',
            /* and here's our custom image picker*/

        }
        $(document).ready(function() {
            tinymce.init(editor);
            $('#fullpage-loader').addClass('d-none')
        });

        $('.add-cancel').on('click', function(){
            $('#form').trigger("reset")
        })

		$(".readonlyinput").on('keydown paste focus mousedown', function(e){
			if(e.keyCode != 9) // ignore tab
				e.preventDefault();
		});
    </script>
		<script>
			tinymce.init({
				selector: ".planeEditor",
			});
		  </script>
	  
		<!--end::Javascript-->
		@stack('scripts')
	</body>
	<!--end::Body-->
</html>