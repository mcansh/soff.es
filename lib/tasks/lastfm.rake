namespace :lastfm do
  task :update => :environment do
    require 'net/http'
    require 'rexml/document'
    
    albums = []
    top_albums_url = "http://ws.audioscrobbler.com/2.0/?method=user.getweeklyalbumchart&user=#{LAST_FM_USERNAME}&api_key=#{LAST_FM_API_KEY}"
    albums_xml = REXML::Document.new(Net::HTTP.get_response(URI.parse(top_albums_url)).body)
    albums_xml.elements.each('lfm/weeklyalbumchart/album') do |album|
      if albums.length < 8
        
        album_name = album.elements['name'].text
        artist_name = album.elements['artist'].text
        image_url = nil
        
        if album_name
          album_art_url = "http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=#{LAST_FM_API_KEY}&artist=#{CGI.escape(artist_name)}&album=#{CGI.escape(album_name)}"
          
          album_art_xml = REXML::Document.new(Net::HTTP.get_response(URI.parse(album_art_url)).body)
          image_url = album_art_xml.elements['lfm/album/image[@size="medium"]'].text
        end

        albums << {
          :name => album_name,
          :image_url => image_url,
          :artist_name => artist_name,
          :url => album.elements['url'].text
        }
      end
    end
    
    if CACHE.class == Hash
      CACHE["lastfm_weekly_album_chart"] = albums
    else
      CACHE.set("lastfm_weekly_album_chart", albums, 7.day)
    end
    puts "Successfully cached #{albums.length} albums"
  end
end