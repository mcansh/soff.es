require 'rubygems'
require 'bundler'
Bundler.require

require 'httparty'
require 'json'
require 'instagram'
require 'rdio_api'

desc 'Update all of the things'
task :update => [:'update:instagram', :'update:blog', :'update:rdio']

namespace :update do
  desc 'Get Instagram photos'
  task :instagram do
    response = HTTParty.get("https://api.instagram.com/v1/users/68907/media/recent/?access_token=#{ENV['INSTAGRAM_TOKEN']}&count=4")
    photos = JSON(response.body)['data']
    redis['instagram'] = JSON.dump(photos)
    puts 'Done! Cached Instagram photos.'
  end

  desc 'Store my latest post in Redis'
  task :blog do
    response = HTTParty.get('https://roon.io/api/v1/blogs/sam/posts?limit=1')
    post = JSON(response.body).first

    %w{title excerpt_html url}.each do |key|
      redis.hset 'latest_post', key, post[key]
    end

    puts "Done! Cached `#{post['title']}`"
  end

  desc 'Get my heavy rotation'
  task :rdio do
    client = RdioApi.new(consumer_key: ENV['RDIO_CONSUMER_KEY'], consumer_secret: ENV['RDIO_CONSUMER_SECRET'])
    rotation = client.getHeavyRotation(type: 'albums', user: ENV['RDIO_USER_KEY'])[0...4]
    rotation.each do |album|
      %w{baseIcon releaseDate duration isClean shortUrl canStream embedUrl type price key canSample hits isExplicit artistKey length trackKeys canTether displayDate}. each do |key|
        album.delete key
      end

      album.url = "http://rdio.com#{album.url}"
      album.icon.gsub!('-200.jpg', '-400.jpg')
      album.artist_url = "http://rdio.com#{album.artistUrl}"
      album.delete 'artistUrl'
    end

    redis['rdio_heavy_rotation'] = JSON(rotation)
    puts 'Done! Cached Rdio albums.'
  end
end

def redis
  @redis ||= if ENV['REDISTOGO_URL']
    uri = URI.parse(ENV['REDISTOGO_URL'])
    Redis.new(host: uri.host, port: uri.port, password: uri.password)
  else
    Redis.new
  end
end
