<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTranslate;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslate;
use App\Models\CharacteristicValue;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BooksController extends Controller
{
    private function getBookTranslateFields(){
        return [
            'lang',
            'slug',
            'name',
            'anotation',
            'meta_title',
            'meta_desc',
            'og_title',
            'og_desc',
            'og_img',
        ];
    }

    public function post(Book $book, Request $request){

        $input = $request->except('_token');

        if(isset($input['languages'])){
            $languages = json_decode($input['languages']);
        }

        $input['photo'] = isset($input['photo']) ? $input['photo'] : '';
        
        //-----------------------------------------------------------------
        if( isset($input['save']) ||  isset($input['save_and_exit']) ){
            $generatedSlugs = [];
            foreach ($languages as $lang) {
                $name = $input['name'][$lang] ?? '';

                $slug = Str::slug($name);

                if (in_array($slug, $generatedSlugs)) {
                    return redirect()->back()->withErrors(['slug' => "Назви на різних мовах генерують однаковий slug: $slug"])->withInput();
                }
                
                $generatedSlugs[$lang] = $slug;
            }

            foreach ($generatedSlugs as $lang => $slug) {
                $exists = BookTranslate::where('slug', $slug)->exists();
                
                if ($exists) {
                    return redirect()->back()
                        ->withErrors(['slug' => "Slug '$slug' (мова $lang) вже зайнятий. Потрібно змінити назву"])
                        ->withInput();
                }
            }

            $messages = [
				'required' => 'Поле required: поле має бути заповнене',
			];
			
			$validator = Validator::make($input, [
					'name.*' => 'required|string',
				],$messages);
            
			if ($validator->fails()) {
				return redirect()->route('admin.addBook')->withErrors($validator)->withInput();
			}


            $book->fill($input);
            $book_save = $book->save();

            foreach ($languages as $lang) {
                $book_translate = new BookTranslate;
                $book_translate->lang = $lang;
                $book_translate->book_id = $book->id;
                $book_translate->name = $input['name'][$lang];
                $book_translate->anotation = $input['anotation'][$lang];
                $book_translate->slug = Str::slug($input['name'][$lang]);
                $book_translate->save();
            }


            if(isset($input['book_chars_vals'])){
                $dataToInsert = [];
                $book_char_vals = array_unique($input['book_chars_vals']);
                // dd($book->id);
                
                foreach ($book_char_vals as $char_val_id) {
                    $dataToInsert[] = [
                        'book_id'     => $book->id,
                        'char_val_id' => $char_val_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
                
                DB::table('books_char_val')->insert($dataToInsert);

            }




            if(isset($input['tags'])){
                DB::table('books_tags')->where('book_id', $input['id'])->delete();
                $dataToInsert = [];
                $book_tags = array_unique($input['tags']);
    
                foreach ($book_tags as $tag_id) {
                    $dataToInsert[] = [
                        'book_id'     => $book->id,
                        'tag_id' => $tag_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
    
                DB::table('books_tags')->insert($dataToInsert);
            }



            if( $book_save ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.books')->with('status','Додано');
                }else{
                    return redirect()->route('admin.addBook')->with('status','Додано');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $book = Book::find($input['id']);
            
            $book->fill($input);
            $book_update = $book->update();

            $languages = json_decode($input['languages']);

            foreach ($languages as $lang) {
                $data = [
                    'name' => $input['name'][$lang]
                ];

                $fields = $this->getBookTranslateFields();

                foreach ($fields as $field) {
                    if( isset($input[$field]) && isset($input[$field][$lang]) ){
                        $data[$field] = $input[$field][$lang];
                    }
                    
                }

                BookTranslate::updateOrCreate(
                    [
                        'book_id' => $input['id'],
                        'lang' => $lang
                    ],
                    $data
                );
            }
            

            if(isset($input['book_chars_vals'])){
                DB::table('books_char_val')->where('book_id', $input['id'])->delete();
                $dataToInsert = [];
                $book_char_vals = array_unique($input['book_chars_vals']);
                
                foreach ($book_char_vals as $char_val_id) {
                    $dataToInsert[] = [
                        'book_id'     => $input['id'],
                        'char_val_id' => $char_val_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
    
                DB::table('books_char_val')->insert($dataToInsert);
            }





            if(isset($input['tags'])){
                DB::table('books_tags')->where('book_id', $input['id'])->delete();
                $dataToInsert = [];
                $book_tags = array_unique($input['tags']);
    
                foreach ($book_tags as $tag_id) {
                    $dataToInsert[] = [
                        'book_id'     => $input['id'],
                        'tag_id' => $tag_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
    
                DB::table('books_tags')->insert($dataToInsert);
            }




			if( $book_update ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.books')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewBook',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = Book::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.books')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.books.list')){
				$search = $input['search'];
				$paginate = 25;
				

                // $items = Book::where(function($query) use ($search) {
                //                         $query->orWhere('name', 'LIKE', '%'.$search.'%')
                //                             ->orWhere('email', 'LIKE', '%'.$search.'%');
                //                         })
                //                         ->paginate($paginate);
                
				$items = Book::where(function($query) use ($search) {
                        $query->whereHas('translates', function($q) use ($search) {
                            $q->where(function($innerQ) use ($search) {
                                $innerQ->where('name', 'LIKE', '%'.$search.'%')
                                        ->orWhere('anotation', 'LIKE', '%'.$search.'%')
                                        ->orWhere('slug', 'LIKE', '%'.$search.'%');
                            });
                        });
                        
                        // $query->orWhere('email', 'LIKE', '%'.$search.'%'); 
                    })
                    ->with('translates') 
                    ->paginate($paginate); 
                
                $items->getCollection()->transform(function ($book) {
                    $book->setRelation('translates', $book->translates->keyBy('lang'));
                    return $book;
                });


                if( $request['page']==null ){
					$request['page'] = 1;
				}
				$page = $paginate * ($request['page']-1);
                
                $data = [
                        'title' => 'Пользователи',
                        'items' => $items,
                        'search' => $search,
						'page' => $page
                    ];
				return 	view('admin.books.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.books');
    }


    public function view($id){
		if(view()->exists('admin.books.edit')){
            $item = Book::where('id', '=', $id)->first();
            $item->setRelation('translates', $item->translates->keyBy('lang'));

            $characteristics = Characteristic::get();
            $characteristics->transform(function ($char) {
                $char->setRelation('translates', $char->translates->keyBy('lang'));
                return $char;
            });
            $chars_vals = CharacteristicValue::get();
            $chars_vals->transform(function ($char_val) {
                $char_val->setRelation('translates', $char_val->translates->keyBy('lang'));
                return $char_val;
            });

            $book_chars_vals = DB::table('books_char_val')->where('book_id', $id)->get();

            $tags = Tag::where('active', 1)->get();
            $tags->transform(function ($tag) {
                $tag->setRelation('translates', $tag->translates->keyBy('lang'));
                return $tag;
            });

            $current_tags = $item->tags;
            $current_tags->transform(function ($tag) {
                $tag->setRelation('translates', $tag->translates->keyBy('lang'));
                return $tag;
            });


            $data = [
					'title' => 'Редагувати книгу',
					'item' => $item,
                    'characteristics' => json_encode($characteristics),
                    'chars_vals' => json_encode($chars_vals),
                    'book_chars_vals' => $book_chars_vals,
                    'tags' => $tags,
                    'current_tags' => $current_tags
			];

			return 	view('admin.books.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.books.edit') ){
            $characteristics = Characteristic::get();
            $characteristics->transform(function ($char) {
                $char->setRelation('translates', $char->translates->keyBy('lang'));
                return $char;
            });
            $chars_vals = CharacteristicValue::get();
            $chars_vals->transform(function ($char_val) {
                $char_val->setRelation('translates', $char_val->translates->keyBy('lang'));
                return $char_val;
            });


            $tags = Tag::where('active', 1)->get();
            $tags->transform(function ($tag) {
                $tag->setRelation('translates', $tag->translates->keyBy('lang'));
                return $tag;
            });

            $data = [
                'title' => 'Додати книгу',
                'characteristics' => json_encode($characteristics),
                'chars_vals' => json_encode($chars_vals),
                'tags' => json_encode($tags)
            ];

			return 	view('admin.books.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.books.list')){

			$paginate = 25;

            $items = Book::with('translates')->paginate($paginate);
            $items->getCollection()->transform(function ($book) {
                $book->setRelation('translates', $book->translates->keyBy('lang'));
                return $book;
            });



			if( $request['page']==null ){
				$request['page'] = 1;
			}

			$page = $paginate * ($request['page']-1);

			$data = [
					'title' => 'Книги',
					'items' => $items,
					'search' => '',
					'page' => $page
				];
			return 	view('admin.books.list',$data);
		}
		abort(404);
	}
}
