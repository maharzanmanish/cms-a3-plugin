<?php
/*
Plugin Name: M-ZipUploader
Description: CMS Assignment 3 - Upload bulk images to Media library from zip
Author: Manish Maharjan
Version: 
Author URI:
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/



add_action( 'admin_menu', 'm_media_file_unzip' );


function m_media_file_unzip()
{
	add_menu_page('M-Zip-Upload', 'M-Zip Upload','manage_options','m_upload_zip','m_upload_zip','dashicons-images-alt',10 );
}

function m_allowed_file_types($filetype)
{
	// Array of allowed file types by MIME
	$allowed_file_types = array('image/png','image/jpeg','image/jpg','image/gif');
	if(in_array($filetype,$allowed_file_types))
	{
		return true;
	} else {
		return false;
	}
}

function m_upload_zip()
{
	echo "<h3>Upload your zip file images</h3>";
	echo "<h3>Supported Image Type : .png, .jpeg, .jpg, .gif, </h3>"; 

	
			if(isset($_FILES['fileToUpload'])) {

								
				// Get the current uploads dir
					$dir 			= "../wp-content/uploads" . wp_upload_dir()['subdir'];
				
				// upload the zip file to the uploads directory 
					$target_file 	= $dir . '/' . basename($_FILES["fileToUpload"]["name"]);
					move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
					$file_name 		= basename( $_FILES["fileToUpload"]["name"]);
					
				// Create zip object
					$zip 			= new ZipArchive;
				
				// open the zip file.
					$res 			= $zip->open($target_file);
					
				// Extract to $dir
					if ($res === TRUE) {
					  $zip->extractTo($dir);

						echo "<h3 style='color:#090;'>The zip file $file_name was successfully unzipped to  " . wp_upload_dir()['url'] . ".</h3>";

						// get number of file then display
						echo "There are ".$zip->numFiles." files in this zip file. <br/>";

						// Loop
						for($i=0; $i < $zip->numFiles; $i++) {
							
							//  URL of the media file.
								$media_file_name = wp_upload_dir()['url'] . '/' . $zip->getNameIndex($i);
							
							// get file type
								$filetype 	= wp_check_filetype( basename( $media_file_name ), null );
								$allowed 	= m_allowed_file_types($filetype['type']);
							
							if($allowed) {
							// uploaded file link
								echo '<a href="'.$media_file_name.'" target="_blank">' . $media_file_name . '</a> Type: '.$filetype['type'].'<br/>';
							
								// attachment information array to the media library.
								$attachment = array(
									'guid'           => $media_file_name, 
									'post_mime_type' => $filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', $zip->getNameIndex($i) ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);

								// Insert 
								$attach_id = wp_insert_attachment( $attachment, $dir . '/' . $zip->getNameIndex($i) );

								// metadata
								$attach_data = wp_generate_attachment_metadata( $attach_id, $dir . '/' . $zip->getNameIndex($i ));
								wp_update_attachment_metadata( $attach_id, $attach_data );							
							} else {
								echo $zip->getNameIndex($i) . ' could not be uploaded. Its file type of ' . $filetype['type'] . ' is now allowed.<br/>' ;
							}
							
						}
						

						echo "</ul>";
					
						
					} else {
						echo "<h3 style='color:#F00;'>The zip file was NOT successfully unzipped.</h3>";
					}			

					$zip->close();
	
			}
            
	       $m_url=get_bloginfo('url');

    
    /*ehco with single code doesn't recognize the variable inside */
    
    /*    			echo ('
            
			<form style="margin-top:20px;" method="post" action="/wp-admin/admin.php?page=m_upload_zip" 
			enctype="multipart/form-data" class="server-form">
			Select ZIP File: <input type="file" name="fileToUpload" id="fileToUpload">
			<div style="" class="submit-button-section">
			<input style="opacity:1;" type="submit" class="deploy-buttons" value="Upload ZIP File" name="submit">
			</div>				
			</form>
			');
    
    */
    
    echo ("
            <form style='margin-top:20px;' method='post' action='{$m_url}/wp-admin/admin.php?page=m_upload_zip' 
			enctype='multipart/form-data' class='server-form'>
			Select ZIP File: <input type='file' name='fileToUpload' id='fileToUpload'>
			<div style='' class='submit-button-section'>
			<input style='opacity:1;' type='submit' class='deploy-buttons' value='Upload Zip' name='submit''>
			</div>				
			</form>
			");
  	
}