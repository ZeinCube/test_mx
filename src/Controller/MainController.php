<?php

namespace App\Controller;

use App\Database\FiasDatabasePGSQLService;
use Symfony\Component\HttpFoundation\Response;

class MainController extends BaseController
{
    public function index(): Response
    {
        $fiasService = new FiasDatabasePGSQLService();
        $searchResults = [];
        $error = '';
        
        if ($this->request->getMethod() === 'POST') {
            try {
                $regionName = trim($this->request->request->get('region', ''));
                $cityName = trim($this->request->request->get('city', ''));
                $streetName = trim($this->request->request->get('street', ''));
                $houseNumber = trim($this->request->request->get('house', ''));

                if (!empty($regionName) || !empty($cityName) || !empty($streetName) || !empty($houseNumber)) {
                    $searchResults = $fiasService->search($regionName, $cityName, $streetName, $houseNumber);
                }

            } catch (\Exception $e) {
                $error = 'Ошибка поиска: ' . $e->getMessage();
            }
        }
        
        $content = '
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Test MX - Поиск адресов ФИАС</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #333; text-align: center; margin-bottom: 30px; }
                .nav { margin: 20px 0; text-align: center; }
                .nav a { display: inline-block; margin: 0 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                .nav a:hover { background: #0056b3; }
                .search-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
                .form-group { flex: 1; }
                .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
                .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                .btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                .btn:hover { background: #218838; }
                .btn-clear { background: #6c757d; margin-left: 10px; }
                .btn-clear:hover { background: #5a6268; }
                .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
                .results { margin-top: 20px; }
                .results h3 { color: #333; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f8f9fa; font-weight: bold; color: #333; }
                tr:hover { background-color: #f5f5f5; }
                .no-results { text-align: center; color: #666; padding: 20px; }
                .info { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .hierarchy-info { font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🔍 Поиск адресов ФИАС</h1>
                <form method="POST" class="search-form">
                    <h3>Поиск по адресу</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="region">Регион:</label>
                            <input type="text" id="region" name="region" placeholder="Например: Москва, Московская область" value="' . htmlspecialchars($this->request->request->get('region', '')) . '">
                        </div>
                        <div class="form-group">
                            <label for="city">Город:</label>
                            <input type="text" id="city" name="city" placeholder="Например: Москва, Санкт-Петербург" value="' . htmlspecialchars($this->request->request->get('city', '')) . '">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="street">Улица:</label>
                            <input type="text" id="street" name="street" placeholder="Например: Тверская, Ленина" value="' . htmlspecialchars($this->request->request->get('street', '')) . '">
                        </div>
                        <div class="form-group">
                            <label for="house">Дом:</label>
                            <input type="text" id="house" name="house" placeholder="Например: 1, 15А" value="' . htmlspecialchars($this->request->request->get('house', '')) . '">
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn">🔍 Найти</button>
                        <button type="button" class="btn btn-clear" onclick="clearForm()">🗑️ Очистить</button>
                    </div>
                </form>';
        
        if ($error) {
            $content .= '<div class="error">' . htmlspecialchars($error) . '</div>';
        }
        
        if (!empty($searchResults)) {
            $content .= '
                <div class="results">
                    <h3>📋 Результаты поиска (' . count($searchResults) . ' записей)</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Регион</th>
                                <th>Город</th>
                                <th>Улица</th>
                                <th>Дом</th>
                                <th>Почтовый индекс</th>
                                <th>GUID</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($searchResults as $record) {
                $regionDisplay = !empty($record->region_name) ? $record->region_shortname . ' ' . $record->region_name : $record->regioncode;
                $cityDisplay = !empty($record->city_name) ? $record->city_shortname . ' ' . $record->city_name : '-';
                $streetDisplay = !empty($record->street_name) ? $record->street_shortname . ' ' . $record->street_name : '-';
                $houseDisplay = !empty($record->house_name) ? $record->house_shortname . ' ' . $record->house_name : '-';
                
                $content .= '
                            <tr>
                                <td>' . htmlspecialchars($regionDisplay) . '</td>
                                <td>' . htmlspecialchars($cityDisplay) . '</td>
                                <td>' . htmlspecialchars($streetDisplay) . '</td>
                                <td>' . htmlspecialchars($houseDisplay) . '</td>
                                <td>' . htmlspecialchars($record->postalcode) . '</td>
                                <td style="font-size: 11px;">' . htmlspecialchars($record->aoguid) . '</td>
                            </tr>';
            }
            
            $content .= '
                        </tbody>
                    </table>
                </div>';
        } elseif ($this->request->getMethod() === 'POST') {
            $content .= '<div class="no-results">🔍 По вашему запросу ничего не найдено</div>';
        }
        
        $content .= '
                <div class="info">
                    <h3>ℹ️ Информация о системе:</h3>
                    <p><strong>PHP версия:</strong> ' . PHP_VERSION . '</p>
                    <p><strong>Время запроса:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>URI:</strong> ' . $this->request->getRequestUri() . '</p>
                    <p><strong>Статус БД:</strong> ' . ($fiasService->testConnection() ? '✅ Подключено' : '❌ Ошибка подключения') . '</p>
                    <p class="hierarchy-info"><strong>Примечание:</strong> Поиск использует JOIN\'ы для отображения полной иерархии адресов</p>
                </div>
            </div>
            
            <script>
                function clearForm() {
                    document.getElementById("region").value = "";
                    document.getElementById("city").value = "";
                    document.getElementById("street").value = "";
                    document.getElementById("house").value = "";
                }
            </script>
        </body>
        </html>';
        
        return $this->render($content);
    }
}