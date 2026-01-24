<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;


use App\Models\EquipmentRequest;
use App\Models\Message;

use App\Models\News;


use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Validator;

class CompanyAdminController extends Controller
{
    
    public function index(User $user, Request $request, $companyId){
        $user = User::where('id', '=', $companyId)->firstOrFail();
        $clients = User::where('role', '=', '0')->where('active',1)->get();

        $data = [
            'user' => $user,
            'clients' => $clients,
            'companyId' => $companyId
        ];


        return 	view('company_admin.index', $data);
    }


    public function client($id, $companyId) {
        $client = User::where('id', $id)->where('active', 1)->firstOrFail(); 

        // СТРОГИЙ ПОРЯДОК ID
        $chat = Conversation::where('user_one_id', $companyId) // Убедитесь, что это всегда user_one_id
            ->where('user_two_id', $id) // Убедитесь, что это всегда user_two_id
            // ЖАДНО загружаем сообщения, отсортированные по убыванию (новые сверху)
            ->with(['messages' => function ($query) {
                $query->latest(); 
            }, 'userOne', 'userTwo']) 
            ->first();

        $messages = collect(); 
        $activeConversation = null;

        if ($chat) {
            $activeConversation = $chat;
            
            $messages = $activeConversation->messages->values()->map(function ($message) use ($companyId) {
                $message->is_sender = $message->sender_id === Auth::id();
                return $message;
            });
        }

        $equipmentsRequests = EquipmentRequest::where('user_id', $id)
            ->where('active', 1)
            ->latest()
            ->get();

        $data = [
            'client' => $client, 
            'chat' => $chat,     // текущий
            'messages' => $messages, // Коллекция сообщений только из ПЕРВОГО чата
            'equipmentsRequests' => $equipmentsRequests, 
            'user' => User::where('id', $companyId)->firstOrFail(),
            'companyId' => $companyId
        ];

        // В шаблоне 'company_admin.company' теперь доступна переменная $messages
        return view('company_admin.client', $data);
    }

    public function addMessage(Request $request){

        $request->validate([
            'receiver_id' => 'required|exists:users,id|not_in:' . Auth::id(),
            'content' => 'required|string|max:2000',
        ], [
            'receiver_id.not_in' => 'Вы не можете отправить сообщение самому себе.'
        ]);

        $senderId = Auth::id();
        $receiverId = (int) $request->receiver_id;
        // $companyId = $request->company_id;
        $content = $request->input('content');

        // // 2. Нормализация ID для поиска (самый маленький ID идет первым)
        // $userOneId = min($senderId, $receiverId);
        // $userTwoId = max($senderId, $receiverId);
         // СТРОГИЙ ПОРЯДОК ID
        $conversation = Conversation::firstOrCreate([
            'user_one_id' => $senderId,
            'user_two_id' => $receiverId,
        ]);
        
        // 4. Создание сообщения
        $message = $conversation->messages()->create([
            'sender_id' => $senderId,
            'content' => $content,
        ]);

        // $client = User::where('id', '=', $receiverId)->firstOrFail();

        return redirect()->route('admin.companyAdminClient', ['id' => $receiverId, 'companyId' =>  Auth::id()]);
    }


    public function search(Request $request, $companyId){
        $search = trim($request->input('search'));
        $perPage = 25;
        $user = User::where('id', $companyId)->first();

        if(!$search){
            // Создание пустого пагинатора, без запроса к БД
            $resultSearch = new LengthAwarePaginator(
                new Collection(), 
                0,
                $perPage,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

        } else {
            $searchPattern = '%' . $search . '%';
            $resultSearch = EquipmentRequest::where('country', 'LIKE', $searchPattern)
                ->with('user')
                ->where('active', 1)
                ->paginate($perPage)
                ->appends(['search' => $search]);
        }

        $data = [
            'search' => $search,
            'resultSearch' => $resultSearch,
            'user' => $user,
            'companyId' => $companyId
        ];

        return view('company_admin.search', $data);
    }


    public function equipment($companyId){
        $user = User::where('id', $companyId)->first();

        $e = EquipmentRequest::with('user')
            ->where('active', 1)
            ->whereHas('user', function ($query) {
                $query->where('active', 1);
            })
            ->paginate(25);
        
        $countriesWithCount = EquipmentRequest::select('country', DB::raw('COUNT(*) as count'))
            ->where('active', 1)
            ->whereHas('user', function ($query) {
                $query->where('active', 1);
            })
            ->groupBy('country')
            ->orderBy('country', 'asc') 
            ->get();


        $data = [
            'resultSearch' => $e,
            'countries' => $countriesWithCount,
            'user' => $user,
            'companyId' => $companyId
        ];


        return view('company_admin.equipment', $data);
    }


    public function equipmentSearch(Request $request, $filterStr, $companyId){
  
        $allowedCountries = explode('|', $filterStr);

        $e = EquipmentRequest::whereIn('country', $allowedCountries)
            ->where('active', 1)
            ->whereHas('user', function ($query) {
                $query->where('active', 1);
            })
            ->with('user')
            ->paginate(25);

        $countriesWithCount = EquipmentRequest::select('country', DB::raw('COUNT(*) as count'))
            ->where('active', 1)
            ->whereHas('user', function ($query) {
                $query->where('active', 1);
            })
            ->groupBy('country')
            ->orderBy('country', 'asc') 
            ->get();


        $data = [
            'resultSearch' => $e,
            'countries' => $countriesWithCount,
            'allowedCountries' => $allowedCountries,
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId
        ];


        return view('company_admin.equipment', $data);
    }

    public function reference($companyId){
        $today = Carbon::today();
        $news = News::whereDate('public_date', '<=', $today)
            ->where('type', 'support')
            ->orWhere('type', 'rules')
            ->where('active', 1)
            ->orderBy('public_date', 'desc')
            ->get();

        $data =  [
            'news' => $news,
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId
        ];

        return view('company_admin.reference', $data);
    }



    public function partnersList($companyId){
        $partners = User::where('role', 1)->where('active', 1)->where('is_service', 0)->whereNot('id', $companyId)->get();
        $data = [
            'partners' => $partners,
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId
        ];


        return view('company_admin.partners', $data);
    }


    public function partnerSingle($id, $companyId){
        $partner = User::where('id', $id)->where('active', 1)->firstOrFail(); 

        // СТРОГИЙ ПОРЯДОК ID
        $chat = Conversation::where('user_one_id', $id) // Убедитесь, что это всегда user_one_id
            ->where('user_two_id', Auth::id()) // Убедитесь, что это всегда user_two_id
            // ЖАДНО загружаем сообщения, отсортированные по убыванию (новые сверху)
            ->with(['messages' => function ($query) {
                $query->latest(); 
            }, 'userOne', 'userTwo']) 
            ->first();

        $messages = collect(); 
        $activeConversation = null;
        $currentUserId = Auth::id();

        if ($chat) {
            $activeConversation = $chat;
            
            $messages = $activeConversation->messages->values()->map(function ($message) use ($currentUserId) {
                $message->is_sender = $message->sender_id === $currentUserId;
                return $message;
            });
        }

        $equipmentsRequests = EquipmentRequest::where('user_id', $id)->latest()->get();


        $data = [
            'partner' => $partner, 
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId
        ];
        // В шаблоне 'company_admin.company' теперь доступна переменная $messages
        return view('company_admin.partnerSingle', $data);
    }


    public function service(Request $request, $companyId){
        $services = User::where('is_service', 1)->where('active',1)->get();

        $data =  [
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId,
            'services' => $services
        ];

        return view('company_admin.service', $data);
    }

    public function serviceSingle(Request $request, $id, $companyId){
        $service = User::where('id', $id)->firstOrFail();
        $data = [
            'service' => $service, 
            'user' => User::where('id', $companyId)->first(),
            'companyId' => $companyId
        ];

        return view('company_admin.serviceSingle', $data);
    }

    public function updateUserData(Request $request, $userId){
        if((int)$userId !== Auth::user()->id){
            abort(403);
        }

        $user = User::findOrFail($userId);

        // $request->validate([
        //     'name' => 'required',
        // ], [
        //     'name' => 'Вы не можете отправить сообщение самому себе.'
        // ]);

        $validator = Validator::make($request->all(), [
            // 'name'  => 'required|max:255',
            'email' => [
                'required', 
                'email', 
                Rule::unique('users')->ignore($user->id)
            ],
            // 'description' => 'required|string|max:255',
            'phone' => 'required|max:255',
            'web_page' => 'nullable|string|max:255',
            'contact_person' => 'required|max:255',
            'password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|same:new_password_confirm',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validator->after(function ($validator) use ($request, $user) {
            if ($request->filled('new_password')) {
                if (!Hash::check($request->password, $user->password)) {
                    $validator->errors()->add('password', 'Incorrect old password');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors() 
            ], 422);
        }

        $updateData = [];


        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            $folder = '/upload/' . date('Y-m');
            $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
            $fullRelativePath = $folder . '/' . $filename;

            $file->move(public_path($folder), $filename);

            $updateData['photo'] = $fullRelativePath;
        }

        // $updateData['name'] = $request->name;
        $updateData['email'] = $request->email;
        $updateData['description'] = $request->description;
        $updateData['phone'] = $request->phone;
        $updateData['web_page'] = $request->web_page;
        $updateData['contact_person'] = $request->contact_person;

        if($request->password && $request->new_password === $request->new_password_confirm){
            $updateData['password'] = Hash::make($request->new_password);
        }


        $user->update($updateData);


        return response()->json([
            'message' => 'ok',
            'photo_url' => $user->photo
        ], 201);
    }



}
