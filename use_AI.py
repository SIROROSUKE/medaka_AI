import keras
import numpy as np
from keras.preprocessing import image

#配列の定義
names = ["黒メダカ","ヒメダカ","ハヤの稚魚","楊貴妃メダカ","ラメメダカ"]

load_model = 'C:/xampp/htdocs/DataFile'
# モデル全体をSavedModel形式のファイルから読み込みます。
model = keras.models.load_model(load_model)

# 画像のパスを指定します。
img_path = 'C:/xampp/htdocs/img.jpeg'

# 画像サイズを指定します。
image_size = (224, 224)

# 画像を読み込み、前処理を行います。
img = image.load_img(img_path, target_size=image_size)
x = image.img_to_array(img)
x = np.expand_dims(x, axis=0)
x = x / 255.0

# モデルを使用して画像を分類します。
predictions = model.predict(x)
predicted_classes = np.argsort(predictions[0])[::-1]
confidence_1 = predictions[0][predicted_classes[0]]
reslt_1 = names[predicted_classes[0]]
confidence_2 = predictions[0][predicted_classes[1]]
reslt_2 = names[predicted_classes[1]]
confidence_3 = predictions[0][predicted_classes[2]]
reslt_3 = names[predicted_classes[2]]
confidence_4 = predictions[0][predicted_classes[3]]
reslt_4 = names[predicted_classes[3]]
confidence_5 = predictions[0][predicted_classes[4]]
reslt_5 = names[predicted_classes[4]]

# 分類結果と信頼度を表示します。
print('一番目の結果:', reslt_1,'信頼度:', int(confidence_1 * 100000000) / 1000000,"%")
print('二番目の結果:', reslt_2,'信頼度:', int(confidence_2 * 100000000) / 1000000,"%")
print('三番目の結果:', reslt_3,'信頼度:', int(confidence_3 * 100000000) / 1000000,"%")
print('四番目の結果:', reslt_4,'信頼度:', int(confidence_4 * 100000000) / 1000000,"%")
print('五番目の結果:', reslt_5,'信頼度:', int(confidence_5 * 100000000) / 1000000,"%")