<?php

namespace HieuLe\WordpressXmlrpcClient;

use Illuminate\Log\Writer;

/**
 * Description of WordpressClient
 *
 * @author TrungHieu
 */
class WordpressClient
{

	private $_username;
	private $_password;
	private $_endPoint;
	private $_request;
	private $_response;
	private $_responseHeader = array();
	private $_error;

	/**
	 *
	 * @var \Illuminate\Log\Writer;
	 */
	private $_logger;

	public function __construct($xmlrpcEndPoint, $username, $password, Writer $logger = null)
	{
		$this->_endPoint = $xmlrpcEndPoint;
		$this->_username = $username;
		$this->_password = $password;
		$this->_logger	 = $logger;
	}

	function getResponseHeader()
	{
		return $this->_responseHeader;
	}

	function getErrorMessage()
	{
		return $this->_error;
	}

	function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Retrieve a post of any registered post type. 
	 * 
	 * @param integer $postId	post id
	 * @param array $fields	Optional. List of field or meta-field names to include in response.
	 * @return struct
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPosts
	 */
	function getPost($postId, array $fields = array())
	{
		if (empty($fields))
		{
			$params = array(1, $this->_username, $this->_password, $postId);
		}
		else
		{
			$params = array(1, $this->_username, $this->_password, $postId, $fields);
		}
		if ($this->_sendRequest('wp.getPost', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Retrieve list of posts of any registered post type. 
	 * 
	 * @param array $filters	Optional
	 * @param array $fields	Optional
	 * @return array array of struct
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPosts
	 */
	function getPosts(array $filters = array(), array $fields = array())
	{
		$params = array(1, $this->_username, $this->_password, $filters);
		if (!empty($fields))
		{
			$params[] = $fields;
		}
		if ($this->_sendRequest('wp.getPosts', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Create a new post of any registered post type. 
	 * 
	 * @param string $title	the post title
	 * @param string $body	the post body
	 * @param array $categorieIds	the list of category ids
	 * @param integer $thumbnailId	the thumbnail id
	 * @param array $content	the content array, see more at wordpress documentation
	 * @return integer the new post id
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.newPost
	 */
	function newPost($title, $body, array $categorieIds = array(), $thumbnailId = NULL, array $content = array())
	{
		$default				 = array(
			'post_type'		 => 'post',
			'post_status'	 => 'publish',
			'terms'			 => array(),
		);
		$content				 = array_merge($default, $content);
		$content['post_title']	 = $title;
		$content['post_content'] = $body;

		if ($thumbnailId != NULL)
		{
			$content['post_thumbnail'] = $thumbnailId;
		}
		if (!empty($categorieIds))
		{
			$content['terms']['category'] = $categorieIds;
		}
		$params = array(1, $this->_username, $this->_password, $content);
		if ($this->_sendRequest('wp.newPost', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Edit an existing post of any registered post type. 
	 * 
	 * @param type $postId	the id of selected post
	 * @param type $title	the new title
	 * @param type $body	the new body
	 * @param array $categorieIds	the new list of category ids
	 * @param type $thumbnailId	the new thumbnail id
	 * @param array $content	the advanced array
	 * @return boolean
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.editPost
	 */
	function editPost($postId, $title, $body, array $categorieIds = array(), $thumbnailId = NULL, array $content = array())
	{
		$content['post_title']	 = $title;
		$content['post_content'] = $body;
		if ($customFields != NULL)
		{
			$content['custom_fields'] = $customFields;
		}
		if ($thumbnailId != NULL)
		{
			$content['post_thumbnail'] = $thumbnailId;
		}
		if (!empty($categorieIds))
		{
			$content['terms']['category'] = $categorieIds;
		}
		$params = array(1, $this->_username, $this->_password, $postId, $content);
		if ($this->_sendRequest('wp.editPost', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Delete an existing post of any registered post type. 
	 * 
	 * @param integer $postId	the id of selected post
	 * @return boolean
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.deletePost
	 */
	function deletePost($postId)
	{
		$params = array(1, $this->_username, $this->_password, $postId);
		if ($this->_sendRequest('wp.deletePost', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Retrieve a registered post type. 
	 * 
	 * @param type $postTypeName the post type name
	 * @param array $fields	Optional. List of field or meta-field names to include in response. 
	 * @return struct
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPostType
	 */
	function getPostType($postTypeName, array $fields = array())
	{
		$params = array(1, $this->_username, $this->_password, $postTypeName, $fields);
		if ($this->_sendRequest('wp.getPostType', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Retrieve list of registered post types. 
	 * 
	 * @param array $filter
	 * @param array $fields
	 * @return array	list of struct
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPostTypes
	 */
	function getPostTypes(array $filter = array(), array $fields = array())
	{
		$params = array(1, $this->_username, $this->_password, $filter, $fields);
		if ($this->_sendRequest('wp.getPostTypes', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Retrieve list of post formats. 
	 * 
	 * @return boolean
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPostFormats
	 */
	function getPostFormats()
	{
		$params = array(1, $this->_username, $this->_password);
		if ($this->_sendRequest('wp.getPostFormats', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Retrieve list of supported values for post_status field on posts. 
	 * 
	 * @return array	list of supported post status
	 * 
	 * @see http://codex.wordpress.org/XML-RPC_WordPress_API/Posts#wp.getPostStatusList
	 */
	function getPostStatusList()
	{
		$params = array(1, $this->_username, $this->_password);
		if ($this->_sendRequest('wp.getPostStatusList', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return FALSE;
		}
	}

	function getAuthors()
	{
		$params = array(0, $this->_username, $this->_password);
		if ($this->_sendRequest('wp.getAuthors', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	function getUsersblogs()
	{
		$params = array($this->_username, $this->_password);
		if ($this->_sendRequest('wp.getUsersBlogs', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	function getCategories()
	{
		$params = array(0, $this->_username, $this->_password);
		if ($this->_sendRequest('wp.getCategories', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	function getTags()
	{
		$params = array(0, $this->_username, $this->_password);
		if ($this->_sendRequest('wp.getTags', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	function addCategory($name, $slug, $parent_id, $description)
	{
		$struct	 = array(
			'name'			 => $name,
			'slug'			 => $slug,
			'parent_id'		 => $parent_id,
			'description'	 => $description);
		$params	 = array(0, $this->_username, $this->_password, $struct);
		if ($this->_sendRequest('wp.newCategory', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	function uploadFile($name, $mime, $bits)
	{
		xmlrpc_set_type($bits, 'base64');
		$struct	 = array(
			'name'	 => $name,
			'type'	 => $mime,
			'bits'	 => $bits,
		);
		$params	 = array(1, $this->_username, $this->_password, $struct);
		if ($this->_sendRequest('wp.uploadFile', $params))
		{
			return $this->getResponse();
		}
		else
		{
			return false;
		}
	}

	private function _sendRequest($method, $params)
	{
		$this->_responseHeader	 = array();
		$this->_request			 = xmlrpc_encode_request($method, $params, array('encoding' => 'UTF-8', 'escaping' => 'markup'));
		$context				 = stream_context_create(array('http' => array(
				'method'	 => "POST",
				'header'	 => "Content-Type: text/xml",
				'content'	 => $this->_request
		)));
		$http_response_header	 = array();
		try
		{
			$file = @file_get_contents($this->_endPoint, false, $context);
			if ($file === FALSE)
			{
				$error					 = error_get_last();
				$error					 = $error ? trim($error['message']) : "error";
				$this->_error			 = "file_get_contents: {$error}";
				$this->_responseHeader	 = $http_response_header;
				$this->_logError();
				return false;
			}
		}
		catch (\Exception $ex)
		{
			$this->_error			 = ("file_get_contents: {$ex->getMessage()} ({$ex->getCode()})");
			$this->_responseHeader	 = $http_response_header;
			$this->_logError();
			return false;
		}
		$response = xmlrpc_decode($file);
		if (is_array($response) && xmlrpc_is_fault($response))
		{
			$this->_error = ("xmlrpc: {$response['faultString']} ({$response['faultCode']})");
			$this->_logError();
			return false;
		}
		$this->_response = $response;
		return true;
	}

	private function _logError()
	{
		if ($this->_logger)
		{
			$this->_logger->getMonolog()->error($this->_error, array(
				'endPoint'			 => $this->_endPoint,
				'username'			 => $this->_username,
				'password'			 => $this->_password,
				'response_header'	 => $this->_responseHeader,
				'request'			 => $this->_request,
			));
		}
	}

}