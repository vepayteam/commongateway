from telethon import TelegramClient, sync
import json
import sys

api_id = 1011533                  # API ID (получается при регистрации приложения на my.telegram.org)
api_hash = "fd20525f72b84e753c3e5f61609cdc2f"              # API Hash (оттуда же)
phone_number = "+79127012918"    # Номер телефона аккаунта, с которого будет выполняться код


# Необходимо предварительно авторизоваться, чтобы был создан файл second_account,
# содержащий данные об аутентификации клиента.
client = TelegramClient('vepay', api_id, api_hash)
client.start()
#print(client.get_me().stringify())

channame = 't.me/joinchat/AAAAAEiIEniA8SdT0bcPgA' # канал @telegram
dp = client.get_entity(channame)
#print(dp)
messages = client.get_messages(dp, limit=5)
#print(messages)
data = []
for message in messages:
    data.append({"id": message.id, "date": message.date.strftime("%Y-%m-%d %H:%M:%S"), "message": message.message})
print(json.dumps(data, ensure_ascii=False))
sys.exit()
