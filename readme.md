# Larave-Test

- laravel 5.8 test(mock) 範例~

## init
設定 .env 有關 db 的部份, 調整為自己的設定, 或是改用 `sqlite`

e.g.
```environments
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=test-app
DB_USERNAME=developer
DB_PASSWORD=password
```


```bash
$ git clone https://github.com/cscolabear/laravel-test.git
$ comoser install
$ php artisan migrate:fresh --seed
$ vendor/bin/phpunit

// ps.

// 測試單一檔案
$ vendor/bin/phpunit --filter SubmitLinksTest

// 略過 coverage report
$ vendor/bin/phpunit --no-coverage
```
result
![Screen Shot 2019-06-27 at 17 02 01](https://user-images.githubusercontent.com/4863629/60253114-23212280-98fe-11e9-94c7-0522b5cabe07.png)

coverage report
![Screen Shot 2019-06-27 at 17 02 44](https://user-images.githubusercontent.com/4863629/60253115-23212280-98fe-11e9-9a8c-eafe9c3dd3ea.png)


## Test File /info
- 原文可參考: https://laravel-news.com/your-first-laravel-application
  - 本篇加入檢查 url 是否存在驗證
  -  mock test 上述功能
- 寫入 title, url, description 資料至 link table
  - 為加快寫作先偷懶把 method 直接寫在 `routes/web.php`
- 測試檔 `\Tests\Feature\SubmitLinksTest` 簡介:
   - `guest_can_submit_a_new_link()`: 測試一般資料寫入與寫入後的轉跳
   - `link_is_not_created_if_validation_fails()`: 未填資料時的 laravel validate 失敗錯誤訊息
   - `link_is_not_created_with_an_invalid_url()`: 測試不合法的 url 被正常擋下
   - `max_length_fails_when_too_long()`: 測試輸入資料內容過長時被 laravel validate 擋下
   - `max_length_succeeds_when_under_max()`: 測試輸入資料長度剛好於 255 內
   - `link_is_not_created_with_an_url_cant_be_reached()`: 啟用 url 是否存在, 不存在時正常被擋下; 這裡透過 mock 抽換自定的 laravel validate rule `App\Rules\UrlExists`

- `\Tests\Feature\UrlExistsTest`: mock 抽換 `App\Rules\UrlExistsHelper`
  - `check_url_pass()`: url 實際存在, 通過檢查
  - `check_url_fail()`: url 不存在

### mock
 - 使用依賴注入(Dependency Injection) 後方便使用 Mockery 進行測試

e.g.
```php
$url = 'https://www.google.com.tw';

// 建立 mock `UrlExistsHelper`
$mock_helper = Mockery::mock(\App\Rules\UrlExistsHelper::class);

// 定義預期行為、傳入值和回傳值
$mock_helper->shouldReceive('isExists')->with($url)->once()->andReturn(false);

// 抽換實際執行對象, 當使用到 `UrlExistsHelper` 一律使用剛剛 mock 出來的物件替換
$this->app->instance(\App\Rules\UrlExistsHelper::class, $mock_helper);

```

- 當對像為外部 API 時 (e.g. curl), mock 可以隔離邏輯與 API 結果, 也能加速測試(不需等 API 回傳結果)
