import { NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import { getPool, ensureTables } from "../../../lib/db";
import { setSession } from "../../../lib/session";
export async function POST(req){
 try{
  const {email="",password=""}=await req.json(); const e=email.trim().toLowerCase();
  if(!e||!password)return NextResponse.json({success:false,message:"Please enter your email and password."},{status:400});
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e))return NextResponse.json({success:false,message:"Please enter a valid email address."},{status:400});
  await ensureTables(); const [rows]=await getPool().execute("SELECT id,full_name,email,phone,password_hash FROM users WHERE email=? LIMIT 1",[e]);
  if(!rows[0])return NextResponse.json({success:false,error:"account_not_found",message:"No registered account was found with this email."},{status:404});
  if(!await bcrypt.compare(password,rows[0].password_hash))return NextResponse.json({success:false,error:"incorrect_password",message:"Incorrect password. Please try again."},{status:401});
  setSession(rows[0].id); return NextResponse.json({success:true,message:"Login successful.",user:{id:rows[0].id,name:rows[0].full_name,email:rows[0].email,phone:rows[0].phone}});
 }catch(e){console.error(e);return NextResponse.json({success:false,message:"Unable to sign in right now. Please try again."},{status:500})}
}