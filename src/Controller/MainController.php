<?php

namespace App\Controller;

use App\Database\FiasDatabasePGSQLService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MainController extends BaseController
{
    public function index(): Response
    {
        $fiasService = new FiasDatabasePGSQLService();
        
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
                .search-form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
                .form-group { flex: 1; }
                .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
                .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                .btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
                .btn:hover { background: #218838; }
                .btn:disabled { background: #6c757d; cursor: not-allowed; }
                .btn-clear { background: #6c757d; margin-left: 10px; }
                .btn-clear:hover { background: #5a6268; }
                .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
                .loading { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; text-align: center; }
                .results { margin-top: 20px; }
                .results h3 { color: #333; margin-bottom: 15px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #f8f9fa; font-weight: bold; color: #333; }
                tr:hover { background-color: #f5f5f5; }
                .no-results { text-align: center; color: #666; padding: 20px; }
                .info { background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .hierarchy-info { font-size: 12px; color: #666; }
                .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                
                .pagination { margin-top: 20px; text-align: center; }
                .pagination-info { margin-bottom: 10px; color: #666; font-size: 14px; }
                .pagination-controls { display: flex; justify-content: center; align-items: center; gap: 10px; }
                .page-numbers { display: flex; gap: 5px; }
                .page-number { padding: 8px 12px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; }
                .page-number:hover { background: #f8f9fa; }
                .page-number.active { background: #007bff; color: white; border-color: #007bff; }
                .page-number.disabled { background: #f8f9fa; color: #6c757d; cursor: not-allowed; }
                .btn-secondary { background: #6c757d; }
                .btn-secondary:hover { background: #5a6268; }
                .btn-secondary:disabled { background: #6c757d; cursor: not-allowed; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🔍 Поиск адресов ФИАС</h1>
                <form id="searchForm" class="search-form">
                    <h3>Поиск по адресу</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="region">Регион:</label>
                            <input type="text" id="region" name="region" placeholder="Например: Москва, Московская область">
                        </div>
                        <div class="form-group">
                            <label for="city">Город:</label>
                            <input type="text" id="city" name="city" placeholder="Например: Москва, Санкт-Петербург">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="street">Улица:</label>
                            <input type="text" id="street" name="street" placeholder="Например: Тверская, Ленина">
                        </div>
                        <div class="form-group">
                            <label for="house">Дом:</label>
                            <input type="text" id="house" name="house" placeholder="Например: 1, 15А">
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn" id="searchBtn">🔍 Найти</button>
                        <button type="button" class="btn btn-clear" onclick="clearForm()">🗑️ Очистить</button>
                    </div>
                </form>
                
                <div id="loading" class="loading" style="display: none;">
                    <span class="spinner"></span> Выполняется поиск...
                </div>
                
                <div id="error" class="error" style="display: none;"></div>
                
                <div id="results" class="results" style="display: none;">
                    <h3 id="resultsTitle">📋 Результаты поиска</h3>
                    <table id="resultsTable">
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
                        <tbody id="resultsBody">
                        </tbody>
                    </table>
                    
                    <div id="pagination" class="pagination" style="display: none;">
                        <div class="pagination-info">
                            <span id="paginationInfo">Страница 1 из 1</span>
                        </div>
                        <div class="pagination-controls">
                            <button id="prevPage" class="btn btn-secondary" onclick="changePage(-1)">← Предыдущая</button>
                            <div id="pageNumbers" class="page-numbers"></div>
                            <button id="nextPage" class="btn btn-secondary" onclick="changePage(1)">Следующая →</button>
                        </div>
                    </div>
                </div>
                
                <div class="info">
                    <h3>ℹ️ Информация о системе:</h3>
                    <p><strong>PHP версия:</strong> ' . PHP_VERSION . '</p>
                    <p><strong>Время загрузки:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>Статус БД:</strong> ' . ($fiasService->testConnection() ? '✅ Подключено' : '❌ Ошибка подключения') . '</p>
                </div>
            </div>
            
            <script>
                let currentPage = 1;
                let totalPages = 1;
                let totalCount = 0;
                let currentSearchParams = "";
                
                function clearForm() {
                    document.getElementById("region").value = "";
                    document.getElementById("city").value = "";
                    document.getElementById("street").value = "";
                    document.getElementById("house").value = "";
                    hideResults();
                    currentPage = 1;
                }
                
                function showLoading() {
                    document.getElementById("loading").style.display = "block";
                    document.getElementById("error").style.display = "none";
                    document.getElementById("results").style.display = "none";
                    document.getElementById("searchBtn").disabled = true;
                }
                
                function hideLoading() {
                    document.getElementById("loading").style.display = "none";
                    document.getElementById("searchBtn").disabled = false;
                }
                
                function showError(message) {
                    document.getElementById("error").textContent = message;
                    document.getElementById("error").style.display = "block";
                    document.getElementById("results").style.display = "none";
                }
                
                function hideResults() {
                    document.getElementById("results").style.display = "none";
                    document.getElementById("error").style.display = "none";
                }
                
                function renderResults(data, pagination) {
                    const resultsBody = document.getElementById("resultsBody");
                    const resultsTitle = document.getElementById("resultsTitle");
                    
                    resultsBody.innerHTML = "";
                    
                    if (data.length === 0) {
                        resultsTitle.textContent = "🔍 По вашему запросу ничего не найдено";
                        document.getElementById("results").style.display = "block";
                        document.getElementById("pagination").style.display = "none";
                        return;
                    }
                    
                    currentPage = pagination.current_page;
                    totalPages = pagination.total_pages;
                    totalCount = pagination.total_count;
                    
                    resultsTitle.textContent = `📋 Результаты поиска (${pagination.total_count} записей)`;
                    
                    data.forEach(function(record) {
                        const row = document.createElement("tr");
                        
                        const regionDisplay = record.region_name ? record.region_shortname + " " + record.region_name : record.regioncode;
                        const cityDisplay = record.city_name ? record.city_shortname + " " + record.city_name : "-";
                        const streetDisplay = record.street_name ? record.street_shortname + " " + record.street_name : "-";
                        const houseDisplay = record.house_name ? record.house_shortname + " " + record.house_name : "-";
                        
                        row.innerHTML = `
                            <td>${escapeHtml(regionDisplay)}</td>
                            <td>${escapeHtml(cityDisplay)}</td>
                            <td>${escapeHtml(streetDisplay)}</td>
                            <td>${escapeHtml(houseDisplay)}</td>
                            <td>${escapeHtml(record.postalcode)}</td>
                            <td style="font-size: 11px;">${escapeHtml(record.aoguid)}</td>
                        `;
                        
                        resultsBody.appendChild(row);
                    });
                    
                    updatePagination();
                    
                    document.getElementById("results").style.display = "block";
                }
                
                function updatePagination() {
                    const pagination = document.getElementById("pagination");
                    const paginationInfo = document.getElementById("paginationInfo");
                    const pageNumbers = document.getElementById("pageNumbers");
                    const prevBtn = document.getElementById("prevPage");
                    const nextBtn = document.getElementById("nextPage");
                    
                    if (totalPages <= 1) {
                        pagination.style.display = "none";
                        return;
                    }
                    
                    pagination.style.display = "block";
                    paginationInfo.textContent = "Страница " + currentPage + " из " + totalPages + " (всего " + totalCount + " записей)";
                    
                    prevBtn.disabled = currentPage <= 1;
                    nextBtn.disabled = currentPage >= totalPages;
                    
                    pageNumbers.innerHTML = "";
                    
                    const maxVisiblePages = 5;
                    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
                    
                    if (endPage - startPage + 1 < maxVisiblePages) {
                        startPage = Math.max(1, endPage - maxVisiblePages + 1);
                    }
                    
                    for (let i = startPage; i <= endPage; i++) {
                        const pageBtn = document.createElement("button");
                        pageBtn.className = `page-number ${i === currentPage ? "active" : ""}`;
                        pageBtn.textContent = i;
                        pageBtn.onclick = () => goToPage(i);
                        pageNumbers.appendChild(pageBtn);
                    }
                }
                
                function changePage(delta) {
                    const newPage = currentPage + delta;
                    if (newPage >= 1 && newPage <= totalPages) {
                        goToPage(newPage);
                    }
                }
                
                function goToPage(page) {
                    if (page < 1 || page > totalPages || page === currentPage) {
                        return;
                    }
                    
                    currentPage = page;
                    performSearch();
                }
                
                function escapeHtml(text) {
                    const div = document.createElement("div");
                    div.textContent = text;
                    return div.innerHTML;
                }
                
                function performSearch() {
                    const formData = new FormData(document.getElementById("searchForm"));
                    const params = new URLSearchParams();
                    
                    for (let [key, value] of formData.entries()) {
                        if (value.trim()) {
                            params.append(key, value.trim());
                        }
                    }
                    
                    if (params.toString() === "") {
                        showError("Пожалуйста, заполните хотя бы одно поле для поиска");
                        return;
                    }
                    
                    params.append("page", currentPage);
                    params.append("limit", 100);
                    
                    currentSearchParams = params.toString();
                    showLoading();
                    
                    fetch("/api/search?" + params.toString())
                        .then(response => {
                            if (!response.ok) {
                                throw new Error("HTTP " + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            hideLoading();
                            if (data.error) {
                                showError(data.error);
                            } else {
                                renderResults(data.data, data.pagination);
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            showError("Ошибка при выполнении запроса: " + error.message);
                        });
                }
                
                document.getElementById("searchForm").addEventListener("submit", function(e) {
                    e.preventDefault();
                    currentPage = 1; 
                    performSearch();
                });
            </script>
        </body>
        </html>';
        
        return $this->render($content);
    }
    
    public function apiSearch(): JsonResponse
    {
        try {
            $regionName = $this->request->query->get('region', '');
            $cityName = $this->request->query->get('city', '');
            $streetName = $this->request->query->get('street', '');
            $houseNumber = $this->request->query->get('house', '');
            $page = max(1, (int) $this->request->query->get('page', 1));
            $limit = max(1, min(1000, (int) $this->request->query->get('limit', 100)));
            $offset = ($page - 1) * $limit;

            $databaseService = new FiasDatabasePGSQLService();
            
            if (!$databaseService->testConnection()) {
                return new JsonResponse([
                    'error' => 'Ошибка подключения к базе данных'
                ], 500);
            }

            $results = $databaseService->search($regionName, $cityName, $streetName, $houseNumber, $offset, $limit);
            $totalCount = $databaseService->getTotalCount($regionName, $cityName, $streetName, $houseNumber);
            
            $totalPages = ceil($totalCount / $limit);

            return new JsonResponse([
                'success' => true,
                'data' => $results,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }
}