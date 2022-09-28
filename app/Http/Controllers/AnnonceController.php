<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Annonce;
use Image;
use Illuminate\Support\Facades\Response;
use App\Gouvernorat;
use App\TypeObjet;
use App\Notifications\ReponseSurAnnonce;
use Notification;
use App\User;
use Illuminate\Support\Collection;
class AnnonceController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    //esta função permite exibir anúncios de acordo com seu tipo (perdido/encontrado)
    public function index(){

        if(request('type')=='lost'){
            
            $annonces=Annonce::where('typeAnnonce','lost')->get();
            
        }else{
            $annonces=Annonce::where('typeAnnonce','found')->get();
        }
        $annonces_triee=self::triAnnoncesParProximite($annonces);
        return view('annonces.index',['annonces'=>$annonces_triee]);
        //return response()->json($annonces_triee);
    }
    //esta função é usada para exibir os dados de um anúncio de acordo com seu identidant($id)
    public function show($id){

        $annonce=Annonce::find($id);
        $user=User::find($annonce->user_id);
        return view('annonces.show',['annonce'=>$annonce],['user'=>$user]);
    }
    //esta função é usada para exibir uma interface na qual um anúncio é adicionado
    public function create(){
        $gouvernorats=Gouvernorat::all();
        $typeObjets=TypeObjet::all();
        
        return view('annonces.create',['gouvernorats'=>$gouvernorats],['typeObjets'=>$typeObjets]);
    }
    
    //esta função é usada para adicionar um anúncio no banco de dados com a imagem em forma de link
    /*public function store(){
        
        request()->validate([
           'title'=>'required',
           'image'=>'required',
           'typeObjet'=>'required',
           'localisation'=>'required',
           'body'=>'required'
        ]);
       $annonce=new Annonce();
       $annonce->title=request('title');
       $annonce->image=request('image');
       $annonce->typeAnnonce="lost";
       $id = auth()->user()->id; 
       $annonce->user_id=$id;
       $annonce->typeObjet=request('typeObjet');
       $annonce->localisation=request('localisation');
       $annonce->body=request('body');
       $annonce->save();
       return redirect('/annoncel?type=lost');
    }*/
    //esta função permite adicionar um anúncio ao banco de dados
    public function store(Request $request){
        $request->validate([
            'title'  => 'required',
            'image' => 'required|image|max:2048',
            'typeObjet'=>'required',
            'localisation'=>'required',
            'typeAnnonce'=>'required',
            'body'=>'required'
        ]);

           $image = Image::make($request->file('image')->getRealPath());
           Response::make($image->encode('jpg'));
           $id = auth()->user()->id;
           if(($request->typeAnnonce)=="lost"){
               $typeAnnonce="lost";
           }else{
                $typeAnnonce="found";
           }
           $form_data = array(
            'title'  => $request->title,
            'image' => $image,
            'typeObjet'=>$request->typeObjet,
            'localisation'=>$request->localisation,
            'body'=>$request->body,
            'user_id'=>$id,
            'typeAnnonce'=>$typeAnnonce
           );
           Annonce::create($form_data); 
           self::verifierNotification($id,$request->title,$request->typeObjet,$request->localisation,$typeAnnonce,$request->body);
        return redirect('/annoncel?type=lost');
     }
     
    //esta função é usada para exibir a interface na qual a atualização é feita 
    public function edit($id){
        
        $annonce=Annonce::find($id);
        $typeObjets=TypeObjet::all();
        $gouvernorats=Gouvernorat::all();
        //dd($gouvernorats);
        //return view('annonces.edit',['annonce'=>$annonce],['typeObjets'=>$typeObjets],['gouvernorats'=>$gouvernorats]);
        return view('annonces.edit', compact(['annonce', 'typeObjets','gouvernorats']));
    }
    //esta função permite atualizar um anúncio
    /*public function update($id){
        
        request()->validate([
            'title'=>'required',
            'image'=>'required',
            'typeObjet'=>'required',
            'localisation'=>'required',
            'body'=>'required'
         ]);
        $annonce=Annonce::find($id);
        $annonce->title=request('title');
        $annonce->image=request('image');
        $annonce->typeAnnonce="lost";
        $annonce->typeObjet=request('typeObjet');
        $annonce->localisation=request('localisation');
        $annonce->body=request('body');
        $annonce->save();
        return redirect('/annonce/' . $annonce->id);
    }*/
    public function update(Request $request,$id){
        
        $request->validate([
            'title'  => 'required',
            'typeObjet'=>'required',
            
            'localisation'=>'required',
            'body'=>'required'
        ]);
        $annonce=Annonce::find($id);
        if(($annonce->typeAnnonce)=="lost"){
            $typeAnnonce="lost";
        }else{
             $typeAnnonce="found";
        }
        $annonce->title=request('title');
        //$annonce->image=request('image');
        if($request->file('image')!=null){
            $image = Image::make($request->file('image')->getRealPath());
            Response::make($image->encode('jpg'));
            $annonce->image=$image;
        }
        $annonce->typeAnnonce=$typeAnnonce;
        $annonce->typeObjet=request('typeObjet');
        $annonce->localisation=request('localisation');
        $annonce->body=request('body');
        $annonce->save();
        return redirect('/annonce/' . $annonce->id);
    }
    //esta função permite apagar um anúncio
    public function destroy($id){
        $annonce=Annonce::find($id);
        $annonce->delete();
        return redirect('/annonces');
    }
    //esta função permite exibir anúncios criados pelo usuário conectado
    public function annoncescreated(){
        $id = auth()->user()->id; 
        $annonces=Annonce::where('user_id',$id)->get();
        return view('annonces.showcreated',['annonces'=>$annonces]);
    }
    //esta função permite importar a imagem do banco de dados e codificá-la para exibi-la 
    function fetch_image($annonce_id)
    {
     $annonce = Annonce::findOrFail($annonce_id);
     $image_file = Image::make($annonce->image);
     $response = Response::make($image_file->encode('jpg'));
     $response->header('Content-Type', 'image/jpg');
     return $response;
    }
    //esta função é usada para garantir a funcionalidade de pesquisa de anúncios
    function search(Request $request)
    {
        $search=$request->get('search');
        $typeAnnonce=$request->get('typeAnnonce');
        $annonces=Annonce::where([
            ['title', 'like', '%'.$search.'%'],
            ['typeAnnonce',$typeAnnonce ],
        ])->get();
        return view('annonces.index',['annonces'=>$annonces]);
    }
    //esta função fornece o mecanismo de notificação
    function verifierNotification($id,$title,$typeObjet,$localisation,$typeAnnonce,$body){
        $annonce=Annonce::where([
            ['user_id', $id],
            ['title',$title ],
            ['typeObjet',$typeObjet ],
            ['localisation',$localisation ],
            ['typeAnnonce',$typeAnnonce ],
            ['body',$body ],
        ])->first();
        $publisher=User::find($id);
        $matchs=Annonce::where([
            ['typeObjet',$typeObjet ],
            ['localisation',$localisation ],
            ['typeAnnonce', '!=' ,$typeAnnonce ],
        ])->get();
        if($matchs!=null){
            $targets = collect([]);
            foreach($matchs as $match){
                $User=User::find($match->user_id);
                $targets->push($User);
                Notification::send($publisher,new ReponseSurAnnonce($match->id,$match->title,$match->typeObjet)); 

                
            }
          // dd($publisher);
            Notification::send($targets,new ReponseSurAnnonce($annonce->id,$title,$typeObjet)); 
            
        }
        

    }
    //esta função é usada para exibir as notificações sobre o usuário conectado
    public function notifications(){
        $id = auth()->user()->id;
        $notifications=\DB::table('notifications')->where('notifiable_id',$id)
        ->where('type', 'App\Notifications\ReponseSurAnnonce')->get();
        //dd($notifications);
        return view('annonces.aff_notifications',['notifications'=>$notifications]);
        
    }
    //esta função permite que você exclua uma notificação
    public function destroyNotifiation($id){
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if($notification){$notification->delete();
        }
        return redirect('/notifications');
    }

    //esta função permite marcar uma notificação como lida
    public function MarkAsReadNotifiation($id){
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if($notification){$notification->markAsRead();
        }
        return redirect('/notifications');
    }

    
    //esta função permite marcar uma notificação como não lida
    public function MarkAsUnreadNotifiation($id){
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if($notification){$notification->markAsUnread();
        }
        return redirect('/notifications');
    }

    
    //ordena os anúncios de acordo com a localização do usuário conectado
    public function triAnnoncesParProximite($annonces){
        $ordered = collect([]);
        $location = auth()->user()->adress;
        foreach($annonces as $annonce){
            if($annonce->localisation==$location){$ordered->prepend($annonce);}
            else{$ordered->push($annonce);}
        }
        return $ordered;
    }

}
