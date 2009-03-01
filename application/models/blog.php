<?php defined('SYSPATH') OR die('No direct access allowed.');

class Blog_Model extends Model {

	public function get_posts($page = 1, $limit = 7)
	{
		$sql = 'SELECT p.id, p.title, p.content, p.slug, p.created, COUNT(c.post_id) as comments FROM posts p LEFT JOIN comments c ON c.post_id = p.id GROUP BY p.id ORDER BY p.created DESC';
		
		// Get the total number of posts
		$result = $this->db->query($sql);
		$total = count($result);
		
		// Get the posts for the current page
		$posts = FALSE;		
		if ($total > 0)
		{
			$sql .= ' LIMIT '.(($page - 1) * $limit).', '.$limit;
			$result = $this->db->query($sql);
			$posts = $result->result_array();
		}
		
		return compact('total', 'posts');
	}
	
	public function get_post_by_slug($slug)
	{
		$result = $this->db->query("SELECT id, title, content, slug, created FROM posts WHERE slug = '$slug';");
		if (count($result) != 1)
		{
			return FALSE;
		}
		
		$array = $result->result_array();
		return $array[0];
	}
	
	public function get_comments_by_post_id($id)
	{
		$result = $this->db->query("SELECT id, name, website, content, created FROM comments WHERE post_id = '$id' ORDER BY created ASC;");
		if (count($result) < 1)
		{
			return FALSE;
		}
		
		return $result->result_array();
	}
	
	public function post_comment($id, $post)
	{
		// Make sure we only insert the fields we are expecting
		$data = array
		(
			'post_id' => $id,
			'name' => $post['name'],
			'email' => $post['email'],
			'website' => $post['website'],
			'content' => $post['content']
		);
		
		return $this->db->insert('comments', $data);
	}
}
