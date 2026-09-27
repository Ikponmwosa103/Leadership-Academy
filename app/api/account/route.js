import { NextResponse } from "next/server";
import { getPool, ensureTables } from "../../../lib/db";
import { readSession, clearSession } from "../../../lib/session";
export async function GET(){
 try{const id=readSession();if(!id)return NextResponse.json({success:false,message:"You are not logged in."},{status:401});await ensureTables();const [r]=await getPool().execute("SELECT id,full_name,email,phone,created_at,updated_at FROM users WHERE id=? LIMIT 1",[id]);if(!r[0])return NextResponse.json({success:false,message:"Account not found."},{status:404});return NextResponse.json({success:true,user:r[0]})}catch(e){return NextResponse.json({success:false,message:"Unable to load your account."},{status:500})}
}
export async function DELETE(){try{const id=readSession();if(!id)return NextResponse.json({success:false,message:"You are not logged in."},{status:401});await getPool().execute("DELETE FROM users WHERE id=?",[id]);clearSession();return NextResponse.json({success:true,message:"Account deleted successfully."})}catch(e){return NextResponse.json({success:false,message:"Unable to delete account."},{status:500})}}
