<?php
class Ameex_EasyMultiwebsite_Model_Observer
{
	public function creation(Varien_Event_Observer $observer)
	{
		$postdata = Mage::app()->getRequest()->getPost();
		$websitedetails = $postdata['groups']['setting']['fields'];
		if(isset($websitedetails['storeviewname']['value']['__empty']))
			{
				unset($websitedetails['storeviewname']['value']['__empty']);
			}
		foreach($websitedetails['storeviewname']['value'] as $websitedetail)
			{
				$foldername = $websitedetail['foldername'];
				if (!file_exists($foldername))
				{
					mkdir($foldername, 0777, true);
					chmod($foldername, 0777);
					copy('index.php', $foldername .'/index.php'); // copy index.php to created folder
					copy('.htaccess', $foldername .'/.htaccess'); // copy .htaccess to created folder
					$search = ". '/";
					$replace =". '/../";
					file_put_contents($foldername .'/index.php', str_replace($search, $replace, file_get_contents($foldername .'/index.php'))); // redirect to our folder
				}
				/* to create a root category */
				$categories = Mage::getResourceModel('catalog/category_collection');
				$categories ->addAttributeToFilter('is_active', 1)
							->addAttributeToFilter('name', $foldername)
							->setCurPage(1)->setPageSize(1)
							->load();
				$pId = $categories->getData();
				$pId = $pId[0]['entity_id'];
				if (!$pId) 
				{
				$category = Mage::getModel('catalog/category')->setStoreId(0);
				$rootcategory['name'] = $foldername;
				$rootcategory['path'] = "1";
				$rootcategory['display_mode'] = "PRODUCTS";
				$rootcategory['is_active'] = 1;
				$rootcategory['is_anchor'] = 1;
				$category->addData($rootcategory);
				try
					{
						$category->save();
						$rootcategoryid = $category->getId();
					}
				catch (Exception $e)
					{
						echo $e->getMessage();
					}
				}
				/* to create a website */
				$websitename = $websitedetail['websitename'];
				$websitecode = strtolower($websitename);
				$website = Mage::getModel('core/website')->load($websitecode, 'code');
				if(!($website->getId()))
				{
					$websitedata = Mage::getModel('core/website')->setCode($websitecode)->setName($websitename);
					try
						{
							$websitedata->save();
							$websiteid = $websitedata->getId();
						}
					catch(Exception $e)
						{
							echo $e->getMessage();
						}
						/* to create store */
					$storename = $websitedetail['storename'];
					$storedata = Mage::getModel('core/store_group')->setWebsiteId($websiteid)->setName($storename)->setRootCategoryId($rootcategoryid);
					try
						{
							$storedata->save();
							$storeId=$storedata->getId();
						}
					catch(Exception $e)
						{
							echo $e->getMessage();
						}
					/* assign base url */
					$currentbaseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
					$newbaseurl = $currentbaseurl.$foldername."/";
					$baseurl = "$newbaseurl";
					$unsecurebaseurl = Mage::getModel('core/config')->saveConfig('web/unsecure/base_url',$baseurl,'websites',$websiteid);
					$securebaseurl = Mage::getModel('core/config')->saveConfig('web/secure/base_url',$baseurl,'websites',$websiteid);
					/* assign skin url */
					$skinurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
					$unsecureskinurl = Mage::getModel('core/config')->saveConfig('web/unsecure/base_skin_url',$skinurl,'websites',$websiteid);
					$secureskinurl = Mage::getModel('core/config')->saveConfig('web/secure/base_skin_url',$skinurl,'websites',$websiteid);
					/* assign media url */
					$mediaurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
					$unsecuremediaurl = Mage::getModel('core/config')->saveConfig('web/unsecure/base_media_url',$mediaurl,'websites',$websiteid);
					$securemediaurl = Mage::getModel('core/config')->saveConfig('web/secure/base_media_url',$mediaurl,'websites',$websiteid);
					/*assign js url */
					$jsurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
					$unsecurejsurl = Mage::getModel('core/config')->saveConfig('web/unsecure/base_js_url',$jsurl,'websites',$websiteid);
					$securejsurl = Mage::getModel('core/config')->saveConfig('web/secure/base_js_url',$jsurl,'websites',$websiteid);
					/* to create storeview */
					$storeview = $websitedetail['storeviewname'];
					$storeviewcode = strtolower($storeview);
					$storeviewdata = Mage::getModel('core/store')->setCode($storeviewcode)->setWebsiteId($websiteid)->setGroupId($storeId)->setName($storeview)->setIsActive(1);
					try
					{
						$storeviewdata->save();
						$storeviewId=$storeviewdata->getId();
					}
					catch(Exception $e)
					{
						echo $e->getMessage();
					}
					/* to create subcategory */
					$category = Mage::getModel('catalog/category')->setStoreId($storeviewId);
					$subcategory['name'] = $foldername;
					$subcategory['path'] = "1";
					$subcategory['display_mode'] = "PRODUCTS";
					$subcategory['is_active'] = 1;
					$subcategory['is_anchor'] = 1;
					$category->addData($subcategory);
					$parentCategory = Mage::getModel('catalog/category')->load($rootcategoryid);
					$category->setPath($parentCategory->getPath());
					try
						{
							$category->save();
						}
					catch (Exception $e)
						{
							echo $e->getMessage();
						}
					/* to run our website */
					file_put_contents($foldername .'/index.php', str_replace('store', 
					'website', file_get_contents($foldername .'/index.php'))); 
					file_put_contents($foldername .'/index.php', 
					str_replace("''", 
					"'$websitecode'", file_get_contents($foldername .'/index.php'))); 
				}
			}
			/* delete the created website */
			$deletedata = $postdata['fieldname'];
			$deletedata = strtolower($deletedata);
			$deletefields = explode(",",$deletedata);
			foreach($deletefields as $deletefield)
				{
					$deletefield=trim($deletefield);
					if(!empty($deletefield))
					{
					$store = Mage::getModel('core/website')->load($deletefield, 'code')->getId();
					$categorynames = Mage::getResourceModel('catalog/category_collection')
									->addFieldToFilter('name', $deletefield)
									->getFirstItem();
					$categoryId = $categorynames->getId();
					Mage::register('isSecureArea', true);
					$model = Mage::getModel('core/website')->load($store);
					$model->delete();
					$categorymodel = Mage::getModel('catalog/category')->load($categoryId);
					$categorymodel->delete();
					Mage::unregister('isSecureArea'); 
				}
			}

			$folderdata = $postdata['folder'];
			$folderdata = strtolower($folderdata);
			$folderfields = explode(",",$folderdata);
			foreach($folderfields as $folderfield)
				{
					$folderfield=trim($folderfield);
					if(!empty($folderfield))
						{
							if (is_dir($folderfield))
								{
									$objects = scandir($folderfield);
									foreach ($objects as $object)
										{
											if ($object != "." && $object != "..")
											{
												if (filetype($folderfield."/".$object) == "dir") 
												rrmdir($folderfield."/".$object); 
												else unlink   ($folderfield."/".$object);
											}
										}
										reset($objects);
										rmdir($folderfield);
								}
						}
				}
	}
}
