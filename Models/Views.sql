CREATE OR REPLACE VIEW Track_V AS
SELECT track_id, title, mp3_url, artists.artist_id, name
FROM tracks INNER JOIN artists ON tracks.artist_id = artists.artist_id;

CREATE OR REPLACE VIEW Playlist_V AS
SELECT playlists.playlist_id, playlist_name, position, Track_V.track_id, title, name, mp3_url, artist_id
FROM playlists INNER JOIN playlists_tracks ON playlists.playlist_id = playlists_tracks.playlist_id
INNER JOIN Track_V ON playlists_tracks.track_id = Track_V.track_id;