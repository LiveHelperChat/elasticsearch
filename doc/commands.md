Get version
curl http://localhost:9299

Get current indexes
curl http://localhost:9299/_cat/indices?v

Insert all chats to be reindexed
INSERT INTO lhc_lheschat_index (chat_id) SELECT id from lh_chat